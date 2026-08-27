<?php

function run_test_harness(string $testFile, array $options = [], array $environment = []): array
{
    $environment['TEST_PHP_EXECUTABLE'] = getenv('TEST_PHP_EXECUTABLE');

    foreach (['PATH', 'SystemRoot', 'TEMP', 'TMPDIR'] as $name) {
        if (($value = getenv($name)) !== false) {
            $environment[$name] = $value;
        }
    }

    $process = proc_open(
        [
            getenv('TEST_PHP_EXECUTABLE'),
            dirname(__DIR__, 2) . '/run-tests.php',
            '-q',
            '--no-progress',
            ...$options,
            $testFile,
        ],
        [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['redirect', 1],
        ],
        $pipes,
        null,
        $environment,
    );

    if ($process === false) {
        throw new RuntimeException('Cannot start run-tests.php');
    }

    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    return [proc_close($process), $output];
}
