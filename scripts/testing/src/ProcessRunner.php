<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function dirname;
use function fclose;
use function file_get_contents;
use function implode;
use function in_array;
use function is_array;
use function is_resource;
use function json_decode;
use function proc_open;
use function rewind;
use function stream_get_contents;
use function tmpfile;

final class ProcessRunner
{
    /**
     * @param list<string> $command
     * @param list<int> $successfulStatuses
     */
    public function command(array $command, ?string $directory = null, array $successfulStatuses = [0]): string
    {
        $stdout = tmpfile();
        $stderr = tmpfile();

        if ($stdout === false || $stderr === false) {
            throw new RuntimeException('Could not create command output files');
        }

        $descriptors = [
            0 => ['file', $this->nullDevice(), 'r'],
            1 => $stdout,
            2 => $stderr,
        ];

        $unusedPipes = [];

        $process = proc_open($command, $descriptors, $unusedPipes, $directory, null, ['bypass_shell' => true]);

        if (is_resource($process) === false) {
            throw new RuntimeException('Could not start command: ' . implode(' ', $command));
        }

        $status = $this->wait($process, $command);

        rewind($stdout);
        rewind($stderr);

        $output = stream_get_contents($stdout);
        $error = stream_get_contents($stderr);

        fclose($stdout);
        fclose($stderr);

        if ($output === false || $error === false) {
            throw new RuntimeException('Could not read command output: ' . implode(' ', $command));
        }

        if (in_array($status, $successfulStatuses, true) === false) {
            throw new RuntimeException('Command failed: ' . implode(' ', $command) . "\n" . $error);
        }

        return $output;
    }

    /**
     * @param list<string> $command
     * @param array<string, string> $environment
     */
    public function process(array $command, ?string $directory, array $environment, mixed $stdout, mixed $stderr): int
    {
        $descriptors = [
            0 => ['file', $this->nullDevice(), 'r'],
            1 => $this->outputDescriptor($stdout),
            2 => $this->outputDescriptor($stderr),
        ];

        $unusedPipes = [];

        $process = proc_open($command, $descriptors, $unusedPipes, $directory, $environment, ['bypass_shell' => true]);

        if (is_resource($process) === false) {
            throw new RuntimeException('Could not start command: ' . implode(' ', $command));
        }

        return $this->wait($process, $command);
    }

    /**
     * @param list<string> $command
     * @param array<string, string> $environment
     */
    public function measured(array $command, string $directory, array $environment, string $stdout, string $stderr, string $metrics): PhptRun
    {
        $wrapper = dirname(__DIR__) . '/measure_process.php';

        $status = $this->process(
            [PHP_BINARY, $wrapper, $metrics, ...$command],
            $directory,
            $environment,
            $stdout,
            $stderr,
        );

        $contents = file_get_contents($metrics);

        $measurement = null;

        if ($contents !== false) {
            $measurement = json_decode($contents, true);
        }

        if (is_array($measurement) === false || isset($measurement['time']) === false) {
            throw new RuntimeException("Could not read test metrics: $metrics");
        }

        return new PhptRun($status, $measurement['time'], $measurement['memory']);
    }

    private function nullDevice(): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return 'NUL';
        }

        return '/dev/null';
    }

    /**
     * @param resource $process
     * @param list<string> $command
     */
    private function wait(mixed $process, array $command): int
    {
        $exit = ProcessWaiter::wait($process);

        if ($exit->signal !== null) {
            throw new RuntimeException('Command interrupted: ' . implode(' ', $command));
        }

        return $exit->status;
    }

    private function outputDescriptor(mixed $output): mixed
    {
        if (is_resource($output) === true) {
            return $output;
        }

        return ['file', $output, 'w'];
    }
}
