<?php

declare(strict_types=1);

use PHP\Testing\ProcessWaiter;

require __DIR__ . '/autoload.php';

$null = '/dev/null';
$metrics = $argv[1];
$start = hrtime(true);
$command = array_slice($argv, 2);
$isolated = function_exists('posix_setsid') && posix_setsid() !== -1;

if (PHP_OS_FAMILY === 'Windows') {
    $null = 'NUL';
}

$status = 1;
$unusedPipes = [];
$process = proc_open($command, [0 => ['file', $null, 'r'], 1 => STDOUT, 2 => STDERR], $unusedPipes);

if (is_resource($process) === true) {

    $exit = ProcessWaiter::wait($process, static function (int $signal) use ($process, $isolated): void {
        if ($isolated === true && function_exists('posix_kill') === true) {
            pcntl_signal($signal, SIG_IGN);
            posix_kill(0, $signal);
            return;
        }

        proc_terminate($process, $signal);
    });

    $status = $exit->signal === null ? $exit->status : 128 + $exit->signal;
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
