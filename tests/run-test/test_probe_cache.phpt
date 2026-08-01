--TEST--
Shared test probe cache only caches failures for the same configuration
--FILE--
<?php
require dirname(__DIR__) . '/probe_cache.inc';

function run_probe_cache_process(string $code, array $environment): string
{
    $command = getenv('TEST_PHP_EXECUTABLE_ESCAPED')
        . ' '
        . getenv('TEST_PHP_EXTRA_ARGS')
        . ' -r '
        . escapeshellarg($code);
    $process = proc_open(
        $command,
        [
            1 => ['pipe', 'w'],
            2 => ['redirect', 1],
        ],
        $pipes,
        null,
        $environment,
        ['bypass_shell' => true],
    );
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    if (0 !== $exitCode = proc_close($process)) {
        throw new Exception("PHP subprocess exited with code $exitCode: $output");
    }

    return $output;
}

$cacheDirectory = __DIR__ . '/test_probe_cache_dir_' . getmypid();
mkdir($cacheDirectory);
$environment = getenv();
$environment['TEST_PHP_SHARED_CACHE_DIR'] = $cacheDirectory;

$helper = var_export(dirname(__DIR__) . '/probe_cache.inc', true);
$first = run_probe_cache_process(
    "require $helper; echo ProbeCache::getFailure('service', ['shared'], static fn(): ?string => 'shared failure');",
    $environment,
);
$second = run_probe_cache_process(
    "require $helper; echo ProbeCache::getFailure('service', ['shared'], static function (): ?string { throw new Exception('Probe should not run'); });",
    $environment,
);
echo "$first\n$second\n";

putenv("TEST_PHP_SHARED_CACHE_DIR=$cacheDirectory");

$failureCalls = 0;
$failureProbe = static function () use (&$failureCalls): ?string {
    $failureCalls++;
    return "failure $failureCalls";
};

var_dump(ProbeCache::getFailure('service', ['first'], $failureProbe));
var_dump(ProbeCache::getFailure('service', ['first'], $failureProbe));
var_dump(ProbeCache::getFailure('service', ['second'], $failureProbe));
var_dump($failureCalls);

$successCalls = 0;
$successProbe = static function () use (&$successCalls): ?string {
    $successCalls++;
    return null;
};

var_dump(ProbeCache::getFailure('service', ['available'], $successProbe));
var_dump(ProbeCache::getFailure('service', ['available'], $successProbe));
var_dump($successCalls);

putenv('TEST_PHP_SHARED_CACHE_DIR');
var_dump(ProbeCache::getFailure('service', ['uncached'], static fn(): ?string => 'uncached failure'));
?>
--CLEAN--
<?php
foreach (glob(__DIR__ . '/test_probe_cache_dir_*') ?: [] as $directory) {
    foreach (glob($directory . '/*') ?: [] as $file) {
        @unlink($file);
    }
    @rmdir($directory);
}
?>
--EXPECT--
shared failure
shared failure
string(9) "failure 1"
string(9) "failure 1"
string(9) "failure 2"
int(2)
NULL
NULL
int(2)
string(16) "uncached failure"
