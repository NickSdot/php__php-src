<?php

declare(strict_types=1);

namespace PHP\Testing;

use function constant;
use function defined;
use function function_exists;
use function pcntl_async_signals;
use function pcntl_signal;
use function pcntl_signal_get_handler;
use function proc_close;
use function proc_get_status;
use function proc_terminate;
use function usleep;

final class ProcessWaiter
{
    /** @param resource $process */
    public static function wait(mixed $process, ?callable $terminate = null): ProcessExit
    {
        $terminate ??= static function (int $signal) use ($process): void {
            proc_terminate($process, $signal);
        };

        $interrupted = null;

        /** @var array<int, callable|int> $handlers */
        $handlers = [];
        $asyncSignals = false;

        if (self::supportsSignals() === true) {

            $asyncSignals = pcntl_async_signals();

            foreach (['SIGINT', 'SIGTERM'] as $name) {
                if (defined($name) === false) {
                    continue;
                }

                $signal = constant($name);
                $handlers[$signal] = pcntl_signal_get_handler($signal);

                pcntl_signal($signal, static function (int $signal) use (&$interrupted, $terminate): void {
                    if ($interrupted !== null) {
                        return;
                    }

                    $interrupted = $signal;
                    $terminate($signal);
                }, false);
            }

            pcntl_async_signals(true);
        }

        try {
            do {
                $status = proc_get_status($process);

                if ($status['running'] === true) {
                    usleep(10_000);
                }
            } while ($status['running'] === true);

            $closed = proc_close($process);
            $exitCode = $status['exitcode'];
        } finally {
            foreach ($handlers as $signal => $handler) {
                pcntl_signal($signal, $handler);
            }

            if ($handlers !== []) {
                pcntl_async_signals($asyncSignals);
            }
        }

        if ($closed !== -1) {
            $exitCode = $closed;
        }

        return new ProcessExit($exitCode, $interrupted);
    }

    private static function supportsSignals(): bool
    {
        return function_exists('pcntl_async_signals') === true
            && function_exists('pcntl_signal') === true
            && function_exists('pcntl_signal_get_handler') === true;
    }
}
