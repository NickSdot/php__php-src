<?php

declare(strict_types=1);

$metrics = $argv[1];
$command = array_slice($argv, 2);
$start = hrtime(true);
$null = '/dev/null';

if (PHP_OS_FAMILY === 'Windows') {
    $null = 'NUL';
}

$process = proc_open($command, [0 => ['file', $null, 'r'], 1 => STDOUT, 2 => STDERR], $pipes);
$status = 1;

if (is_resource($process) === true) {
    $status = proc_close($process);
}

$usage = [];

if (function_exists('getrusage') === true) {
    $usage = getrusage(1);
}

$memory = null;

if (is_array($usage) === true) {
    $memory = $usage['ru_maxrss'] ?? null;
}

if ($memory !== null && PHP_OS_FAMILY !== 'Darwin') {
    $memory *= 1024;
}

file_put_contents($metrics, json_encode(['time' => (hrtime(true) - $start) / 1e9, 'memory' => $memory]));
exit($status);
