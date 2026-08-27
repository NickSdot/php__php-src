--TEST--
External shard plans are complete, deterministic, extension-aware, and duration-balanced
--SKIPIF--
<?php
if (!function_exists('proc_open')) {
    die('skip proc_open() is required');
}
?>
--FILE--
<?php
$root = __DIR__ . '/external_shard_plan_' . getmypid();
$runTests = dirname(__DIR__, 2) . '/run-tests.php';
$historyFile = "$root/history.json";

$removeTree = static function (string $directory) use (&$removeTree): void {
    if (!is_dir($directory)) {
        return;
    }
    foreach (scandir($directory) as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $path = $directory . '/' . $entry;
        if (is_dir($path)) {
            $removeTree($path);
        } else {
            unlink($path);
        }
    }
    rmdir($directory);
};
$removeTree($root);

$testPaths = [
    'Zend/tests/a.phpt',
    'ext/standard/tests/e.phpt',
    'ext/standard/tests/f.phpt',
    'sapi/cli/tests/d.phpt',
    'tests/b.phpt',
    'tests/c.phpt',
];
$testContents = '--' . "TEST--\n"
    . "shard fixture\n"
    . '--' . "FILE--\n"
    . "<?php echo \"ok\\n\"; ?>\n"
    . '--' . "EXPECT--\n"
    . "ok\n";
foreach ($testPaths as $path) {
    if (!is_dir(dirname("$root/$path"))) {
        mkdir(dirname("$root/$path"), recursive: true);
    }
    file_put_contents("$root/$path", $testContents);
}

mkdir("$root/ext/duration_shards_unavailable/tests", recursive: true);
file_put_contents("$root/ext/duration_shards_unavailable/tests/ignored.phpt", $testContents);
file_put_contents("$root/tests/.hidden.phpt", $testContents);

$raceTestContents = '--' . "TEST--\n"
    . "concurrent timing writer fixture\n"
    . '--' . "FILE--\n"
    . "<?php usleep(1000000); echo \"ok\\n\"; ?>\n"
    . '--' . "EXPECT--\n"
    . "ok\n";
mkdir("$root/race", recursive: true);
file_put_contents("$root/race/first.phpt", $raceTestContents);
file_put_contents("$root/race/second.phpt", $raceTestContents);

$environment = [
    'NO_INTERACTION' => '1',
    'REPORT_EXIT_STATUS' => '1',
    'SKIP_ONLINE_TESTS' => '1',
    'TEST_PHP_EXECUTABLE' => getenv('TEST_PHP_EXECUTABLE'),
    'TEST_PHP_SRCDIR' => $root,
    'TEST_PHP_TIMINGS' => $historyFile,
];
foreach (['PATH', 'SystemRoot', 'TEMP', 'TMP', 'TMPDIR'] as $name) {
    if (($value = getenv($name)) !== false) {
        $environment[$name] = $value;
    }
}

$start = static function (
    array $arguments,
    array $environmentOverrides = [],
) use ($environment, $runTests): array {
    $process = proc_open(
        [
            getenv('TEST_PHP_EXECUTABLE'),
            $runTests,
            '-P',
            '-q',
            '--no-progress',
            ...$arguments,
        ],
        [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['redirect', 1],
        ],
        $pipes,
        null,
        array_replace($environment, $environmentOverrides),
    );

    if ($process === false) {
        throw new RuntimeException('failed to start run-tests.php');
    }

    fclose($pipes[0]);

    return [$process, $pipes[1]];
};

$finish = static function ($process, $outputPipe): array {
    $output = stream_get_contents($outputPipe);
    fclose($outputPipe);

    return [proc_close($process), $output];
};

$run = static function (
    array $arguments,
    array $environmentOverrides = [],
) use ($start, $finish): array {
    return $finish(...$start($arguments, $environmentOverrides));
};

$check = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$readJson = static function (string $json): stdClass {
    return json_decode($json, flags: JSON_THROW_ON_ERROR);
};

$flattenTests = static function (stdClass $plan): array {
    $tests = [];

    foreach ($plan->runners as $runner) {
        foreach ($runner->workers as $worker) {
            array_push($tests, ...$worker);
        }
    }

    sort($tests, SORT_STRING);

    return $tests;
};

$runnerDurations = static function (stdClass $plan, array $durations): array {
    return array_map(
        static fn(stdClass $runner): int|float => array_sum(array_map(
            static fn(array $worker): int|float => array_sum(array_map(
                static fn(string $test): int|float => $durations[$test],
                $worker,
            )),
            $runner->workers,
        )),
        $plan->runners,
    );
};

$workerDurations = static function (stdClass $plan, array $durations): array {
    $workerDurations = [];

    foreach ($plan->runners as $runner) {
        foreach ($runner->workers as $worker) {
            $workerDurations[] = array_sum(array_map(
                static fn(string $test): int|float => $durations[$test],
                $worker,
            ));
        }
    }

    return $workerDurations;
};

$canonicalize = static function (mixed $value) use (&$canonicalize): mixed {
    if (!is_array($value)) {
        return $value;
    }

    if (array_is_list($value)) {
        return array_map($canonicalize, $value);
    }

    ksort($value, SORT_STRING);

    foreach ($value as &$entry) {
        $entry = $canonicalize($entry);
    }
    unset($entry);

    return $value;
};

$configurationFingerprint = static function (array $configuration) use ($canonicalize): string {
    return hash('sha256', json_encode(
        $canonicalize($configuration),
        JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
    ));
};

[$status, $output] = $run(["$root/tests/b.phpt"]);
$check($status === 0, "configuration seed run failed: $output");
$seededHistory = $readJson(file_get_contents($historyFile));
$check($seededHistory->version === 2, 'timing history was not written as version 2');
$configurations = get_object_vars($seededHistory->configurations);
$check(count($configurations) === 1, 'seed run did not record exactly one configuration');
$fingerprint = array_key_first($configurations);
$configuration = $configurations[$fingerprint]->configuration;

$writeHistory = static function (
    array $durations,
    ?stdClass $configurationOverride = null,
    ?string $fingerprintOverride = null,
) use ($configuration, $fingerprint, $historyFile): void {
    $samples = array_map(
        static fn(int|float $duration): array => array_fill(0, 9, $duration),
        $durations,
    );
    file_put_contents($historyFile, json_encode([
        'version' => 2,
        'configurations' => [
            $fingerprintOverride ?? $fingerprint => [
                'configuration' => $configurationOverride ?? $configuration,
                'tests' => $samples,
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
};

$durations = [
    'Zend/tests/a.phpt' => 9,
    'ext/standard/tests/e.phpt' => 8,
    'ext/standard/tests/f.phpt' => 7,
    'sapi/cli/tests/d.phpt' => 6,
    'tests/b.phpt' => 5,
    'tests/c.phpt' => 4,
    'ext/removed/tests/stale.phpt' => 1000,
];
$writeHistory($durations);

[$status, $output] = $run(['-j2', '--shard-plan', '3']);
$check($status === 0, "duration plan failed: $output");
$plan = $readJson($output);
$check($plan->version === 1, 'unexpected plan version');
$check($plan->runner_count === 3, 'unexpected runner count');
$check($plan->worker_count === 2, 'unexpected worker count');
$check(count($plan->runners) === 3, 'plan does not contain three runners');
foreach ($plan->runners as $runner) {
    $check(count($runner->workers) === 2, 'runner does not contain two worker lanes');
}
$check($flattenTests($plan) === $testPaths, 'plan has omissions, duplicates, or unexpected extension tests');
$check(
    $runnerDurations($plan, $durations) === [13, 13, 13],
    'historical runners are not balanced: ' . $output,
);
$check(max($workerDurations($plan, $durations)) === 9, 'worker plan exceeded the unavoidable longest test');

[$status, $secondOutput] = $run(['-j2', '--shard-plan', '3']);
$check($status === 0, "second duration plan failed: $secondOutput");
$check($output === $secondOutput, 'plan output is not deterministic');

[$status, $output] = $run(['-j2', '--shard-plan', '1']);
$check($status === 0, "path-batched plan failed: $output");
$pathBatchedPlan = $readJson($output);
$check(
    $pathBatchedPlan->runners[0]->workers === [
        array_slice($testPaths, 0, 3),
        array_slice($testPaths, 3, 3),
    ],
    'duration plan did not preserve path-local batches',
);

unset($durations['tests/c.phpt']);
$writeHistory($durations);
[$status, $output] = $run(['-j2', '--shard-plan', '3']);
$check($status === 0, "partial-history plan failed: $output");
$partialPlan = $readJson($output);
$check($flattenTests($partialPlan) === $testPaths, 'new test was omitted from the plan');
$durations['tests/c.phpt'] = 7;
$check(
    $runnerDurations($partialPlan, $durations) === [14, 14, 14],
    'new test was not attached to the globally lightest topology slot',
);

unlink($historyFile);
[$status, $output] = $run(['-j2', '--shard-plan', '3']);
$check($status === 0, "fallback plan failed: $output");
$fallbackPlan = $readJson($output);
$check($fallbackPlan->runners[0]->workers === [[$testPaths[0]], [$testPaths[3]]], 'unexpected fallback runner 1');
$check($fallbackPlan->runners[1]->workers === [[$testPaths[1]], [$testPaths[4]]], 'unexpected fallback runner 2');
$check($fallbackPlan->runners[2]->workers === [[$testPaths[2]], [$testPaths[5]]], 'unexpected fallback runner 3');
$check($flattenTests($fallbackPlan) === $testPaths, 'fallback plan changed coverage');

$writeHistory(['../../outside.phpt' => 1000]);
[$status, $output] = $run(['-j2', '--shard-plan', '3']);
$check($status === 0, "stale-history plan failed: $output");
$stalePlan = $readJson($output);
$check($stalePlan->runners == $fallbackPlan->runners, 'fully stale history did not fall back');
$check($flattenTests($stalePlan) === $testPaths, 'stale history changed coverage');

$otherConfiguration = json_decode(
    json_encode($configuration, JSON_THROW_ON_ERROR),
    true,
    flags: JSON_THROW_ON_ERROR,
);
$otherConfiguration['debug'] = !$otherConfiguration['debug'];
$otherFingerprint = $configurationFingerprint($otherConfiguration);
$writeHistory($durations, (object) $otherConfiguration, $otherFingerprint);
[$status, $output] = $run(['-j2', '--shard-plan', '3']);
$check($status === 0, "different-configuration plan failed: $output");
$differentConfigurationPlan = $readJson($output);
$check(
    $differentConfigurationPlan->runners == $fallbackPlan->runners,
    'timings from a different build configuration were used',
);

file_put_contents($historyFile, json_encode([
    'version' => 1,
    'durations' => ['Zend/tests/a.phpt' => 1000],
], JSON_THROW_ON_ERROR));
[$status, $output] = $run(['-j2', '--shard-plan', '3']);
$check($status === 0, "legacy-history plan failed: $output");
$legacyPlan = $readJson($output);
$check($legacyPlan->runners == $fallbackPlan->runners, 'unscoped version 1 timings were used');

file_put_contents($historyFile, '{"version":1,"durations":{"tests/b.phpt":-1}}');
[$status] = $run(['-j2', '--shard-plan', '3']);
$check($status === 1, 'malformed timing input was accepted');

$writeHistory($durations);
$malformedHistory = $readJson(file_get_contents($historyFile));
$malformedHistory->configurations->{$fingerprint}->tests->{'tests/b.phpt'} = [-1];
file_put_contents($historyFile, json_encode($malformedHistory, JSON_THROW_ON_ERROR));
[$status] = $run(['-j2', '--shard-plan', '3']);
$check($status === 1, 'malformed timing samples were accepted');

file_put_contents($historyFile, '{');
[$status] = $run(['-j2', '--shard-plan', '3']);
$check($status === 1, 'invalid timing JSON was accepted');

unlink($historyFile);
[$status] = $run(['-j2', '--shard-plan', '4']);
$check($status === 1, 'a plan with empty worker lanes was accepted');

file_put_contents($historyFile . '.tmp.stale', 'stale');
$writeHistory($durations);
$workerTests = array_map(static fn(string $path): string => "$root/$path", $testPaths);
[$status, $output] = $run(['-j3', ...$workerTests]);
$check($status === 0, "timing run failed: $output");
$check(str_contains($output, 'Spawning 3 workers'), '-j3 did not create three worker plans');
$timings = $readJson(file_get_contents($historyFile));
$configurationTimings = $timings->configurations->{$fingerprint}->tests;

foreach ($testPaths as $testPath) {
    $check(isset($configurationTimings->{$testPath}), "timing for $testPath was not saved");
    $check(count($configurationTimings->{$testPath}) === 9, "timing window for $testPath was not capped");
}

$savedSamples = $configurationTimings->{'tests/b.phpt'};
$check(end($savedSamples) !== 5, 'new timing was not appended to the rolling window');

$check(glob($historyFile . '.tmp.*') === [], 'temporary timing file was left behind');

$raceHistoryFile = "$root/race-history.json";
$raceEnvironment = ['TEST_PHP_TIMINGS' => $raceHistoryFile];
[$firstProcess, $firstOutputPipe] = $start(['-j1', "$root/race/first.phpt"], $raceEnvironment);
[$secondProcess, $secondOutputPipe] = $start(['-j1', "$root/race/second.phpt"], $raceEnvironment);
[$firstStatus, $firstOutput] = $finish($firstProcess, $firstOutputPipe);
[$secondStatus, $secondOutput] = $finish($secondProcess, $secondOutputPipe);
$check($firstStatus === 0, "first concurrent timing run failed: $firstOutput");
$check($secondStatus === 0, "second concurrent timing run failed: $secondOutput");

$raceTimings = $readJson(file_get_contents($raceHistoryFile));
$raceConfigurations = get_object_vars($raceTimings->configurations);
$check(count($raceConfigurations) === 1, 'concurrent runs recorded different configurations');
$raceTests = reset($raceConfigurations)->tests;
$check(isset($raceTests->{'race/first.phpt'}), 'first concurrent timing was lost');
$check(isset($raceTests->{'race/second.phpt'}), 'second concurrent timing was lost');
$check(glob($raceHistoryFile . '.tmp.*') === [], 'concurrent timing run left a temporary file behind');

[$status, $output] = $run(['-j2', '--shard-plan', '3']);
$check($status === 0, "plan from recorded timings failed: $output");
$recordedPlan = $readJson($output);
$check($flattenTests($recordedPlan) === $testPaths, 'recorded timings changed coverage');

$runnerFile = "$root/runner.txt";
file_put_contents($runnerFile, implode("\n", array_merge(...$recordedPlan->runners[0]->workers)) . "\n");
[$status, $output] = $run(['-j2', '-r', $runnerFile]);
$check($status === 0, "running a generated runner failed: $output");

$removeTree($root);
echo "OK\n";
?>
--CLEAN--
<?php
$root = __DIR__ . '/external_shard_plan_*';
foreach (glob($root) ?: [] as $directory) {
    $removeTree = static function (string $directory) use (&$removeTree): void {
        foreach (scandir($directory) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $directory . '/' . $entry;
            if (is_dir($path)) {
                $removeTree($path);
            } else {
                unlink($path);
            }
        }
        rmdir($directory);
    };
    $removeTree($directory);
}
?>
--EXPECT--
OK
