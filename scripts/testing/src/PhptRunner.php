<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function array_map;
use function count;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function getenv;
use function strrpos;
use function substr;

final class PhptRunner
{
    public function __construct(
        private ProcessRunner $process,
        private Output $output,
        private string $testPhp,
        private string $temporaryDirectory
    ) {}

    /** @param ?list<string> $tests */
    public function run(string $name, string $runner, ?array $tests): PhptRun
    {
        if ($tests === []) {
            $this->output->printLine('Running %s suite (0 tests)', $name);
            return new PhptRun(0, 0.0, 0);
        }

        $command = [PHP_BINARY, $runner, '-q', '-j' . TestCoverageCommand::WORKERS, '-p', $this->testPhp];

        if ($tests === null) {
            $this->output->printLine('Running %s complete suite', $name);
        }

        if ($tests !== null) {
            $command = $this->withSelectedTests($command, $name, $tests);
        }

        $environment = getenv();

        unset($environment['TEST_PHP_ARGS']);

        $environment['NO_INTERACTION'] = '1';
        $environment['REPORT_EXIT_STATUS'] = '1';
        $environment['TEST_PHP_EXECUTABLE'] = $this->testPhp;
        $environment['TEST_PHP_SRCDIR'] = dirname($runner);

        $files = array_map(
            fn(string $suffix): string => "$this->temporaryDirectory/$name-$suffix",
            ['tests.log', 'tests.err', 'metrics.json'],
        );

        return $this->process->measured($command, dirname($runner), $environment, ...$files);
    }

    public function failureOutput(string $name): string
    {
        $stdout = file_get_contents("$this->temporaryDirectory/$name-tests.log");
        $stderr = file_get_contents("$this->temporaryDirectory/$name-tests.err");

        if ($stdout === false || $stderr === false) {
            throw new RuntimeException("Could not read $name test output");
        }

        $summary = strrpos($stdout, 'FAILED TEST SUMMARY');

        if ($summary !== false) {
            $stdout = substr($stdout, $summary);
        }

        return $this->output->line() . $stdout . $stderr;
    }

    /**
     * @param list<string> $command
     * @param list<string> $tests
     * @return list<string>
     */
    private function withSelectedTests(array $command, string $name, array $tests): array
    {
        $list = "$this->temporaryDirectory/$name-tests.list";

        if (file_put_contents($list, $this->output->lines($tests)) === false) {
            throw new RuntimeException("Could not write test list: $list");
        }

        $this->output->printLine('Running %s suite (%d tests)', $name, count($tests));

        return [...$command, '-r', $list];
    }
}
