<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function array_map;
use function count;
use function dirname;
use function file;
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
            $this->output->printLine('Running %s 0 tests', $name);
            return new PhptRun(0, 0.0, 0, 0);
        }

        $results = "$this->temporaryDirectory/$name-results.list";

        $command = [
            PHP_BINARY,
            $runner,
            '-q',
            '-j' . TestCoverageCommand::WORKERS,
            '-p',
            $this->testPhp,
            '-W',
            $results,
        ];

        if ($tests === null) {
            $this->output->printLine('Running %s tests', $name);
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

        $run = $this->process->measured($command, dirname($runner), $environment, ...$files);

        $testCount = $this->testCount($results);

        return new PhptRun($run->status, $run->time, $run->memory, $testCount);
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

        $this->output->printLine('Running %s %d tests', $name, count($tests));

        return [...$command, '-r', $list];
    }

    private function testCount(string $resultsFile): ?int
    {
        $results = file($resultsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($results === false) {
            return null;
        }

        return count($results);
    }
}
