<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function dirname;
use function fclose;
use function file_get_contents;
use function getenv;
use function mkdir;
use function preg_match;
use function rewind;
use function str_contains;
use function stream_get_contents;
use function strlen;
use function strpos;
use function substr;
use function tmpfile;

final readonly class CoverageIntegrationTest
{
    private const string EXTENSION = 'ext/coverage_fixture';
    private const string SOURCE = self::EXTENSION . '/coverage_fixture.c';
    private const string TESTS = self::EXTENSION . '/tests';

    private function __construct(
        private IntegrationTestWorkspace $workspace,
        private ProcessRunner $process,
        public string $base,
        public string $covered,
        public string $unrelated,
        public string $changed,
        public string $negative
    ) {}

    public static function create(string $repo, string $stateFile): self
    {
        $workspace = IntegrationTestWorkspace::create($repo, 'HEAD', $stateFile);
        $fixture = $workspace->path();

        if (mkdir("$fixture/" . self::TESTS, recursive: true) === false) {
            throw new RuntimeException('Could not create integration extension');
        }

        $workspace->write(self::EXTENSION . '/config.m4', self::fixture('config.m4'));
        $workspace->write(self::EXTENSION . '/php_coverage_fixture.h', self::fixture('php_coverage_fixture.h'));
        $workspace->write(self::SOURCE, self::fixture('coverage_fixture.base.c'));
        $workspace->write(self::TESTS . '/coverage_fixture.phpt', self::fixture('coverage_fixture.base.test'));
        $base = $workspace->commit('Add coverage fixture');

        $workspace->write(self::TESTS . '/coverage_fixture.phpt', self::fixture('coverage_fixture.tree.test'));
        $covered = $workspace->commit('Cover both fixture paths');

        $workspace->write(self::EXTENSION . '/README.md', "Coverage fixture\n");
        $unrelated = $workspace->commit('Document coverage fixture');

        $workspace->write(self::SOURCE, self::fixture('coverage_fixture.tree.c'));
        $changed = $workspace->commit('Refactor coverage fixture');

        $workspace->write(self::TESTS . '/coverage_fixture.phpt', self::fixture('coverage_fixture.base.test'));
        $negative = $workspace->commit('Remove fixture coverage');

        $workspace->configure(['--disable-all', '--enable-coverage-fixture']);

        return new self($workspace, new ProcessRunner(), $base, $covered, $unrelated, $changed, $negative);
    }

    public function compare(string $name, string $base, string $tree, bool $missed = false): void
    {
        $group = 'Gained';

        if ($missed === true) {
            $group = 'Missed';
        }

        $this->compareCase($name, $base, $tree, $missed, $group);

        echo "$name: OK\n";
    }

    public function compareUnrelated(string $name, string $base, string $tree, ?string $coverageGroup = null): void
    {
        $output = $this->compareCase($name, $base, $tree, false, $coverageGroup);

        if (str_contains($output, 'Building tree') === true) {
            throw new RuntimeException("$name unexpectedly built tree\n$output");
        }

        echo "$name: OK\n";
    }

    private static function fixture(string $name): string
    {
        $file = __DIR__ . "/fixtures/coverage_extension/$name";
        $contents = file_get_contents($file);

        if ($contents === false) {
            throw new RuntimeException("Could not read integration fixture: $name");
        }

        return $contents;
    }

    /** @return array{int, string} */
    private function run(string $base, string $tree): array
    {
        $environment = getenv();

        if ($environment === false) {
            throw new RuntimeException('Could not read environment');
        }

        $temporary = $this->workspace->temporaryPath();

        $environment['CIRCLECI'] = '1';
        $environment['NO_COLOR'] = '1';
        $environment['TMPDIR'] = $temporary;
        $environment['TMP'] = $temporary;
        $environment['TEMP'] = $temporary;

        unset($environment['PATH_TRANSLATED'], $environment['QUERY_STRING'], $environment['REQUEST_METHOD'], $environment['SCRIPT_FILENAME'], $environment['TEST_PHP_EXECUTABLE']);

        $stream = tmpfile();

        if ($stream === false) {
            throw new RuntimeException('Could not create output stream');
        }

        $status = $this->process->process([
            PHP_BINARY,
            dirname(__DIR__) . '/validate_test_coverage.php',
            '--base', $base,
            '--tree', $tree,
            '--source', self::SOURCE,
            self::TESTS,
        ], $this->workspace->path(), $environment, $stream, $stream);

        rewind($stream);
        $output = stream_get_contents($stream);
        fclose($stream);

        if ($output === false) {
            throw new RuntimeException('Could not read coverage output');
        }

        return [$status, $output];
    }

    private function compareCase(string $name, string $base, string $tree, bool $missed, ?string $coverageGroup): string
    {
        [$status, $output] = $this->run($base, $tree);
        $this->assertOutput($name, $status, $output, $missed, $coverageGroup);

        return $output;
    }

    private function assertOutput(string $name, int $status, string $output, bool $missed, ?string $coverageGroup): void
    {
        $result = 'PASS';
        $expectedStatus = 0;

        if ($missed === true) {
            $expectedStatus = 1;
            $result = 'FAIL';
        }

        if ($status !== $expectedStatus) {
            throw new RuntimeException("$name returned $status instead of $expectedStatus\n$output");
        }

        foreach (['Coverage: ' . self::SOURCE, 'Report: ', "\n$result\n"] as $expected) {
            if (str_contains($output, $expected) === false) {
                throw new RuntimeException("$name output does not contain: $expected\n$output");
            }
        }

        if (preg_match('/^Report: (.+)$/m', $output, $matches) !== 1) {
            throw new RuntimeException("$name output does not contain report path\n$output");
        }

        $report = file_get_contents($matches[1]);

        if ($report === false) {
            throw new RuntimeException("$name report could not be read\n$output");
        }

        if ($coverageGroup === null) {
            return;
        }

        if (str_contains($this->reportGroup($report, $coverageGroup), '      ' . self::SOURCE . ":\n") === false) {
            throw new RuntimeException("$name report does not contain fixture source\n$output");
        }
    }

    private function reportGroup(string $report, string $name): string
    {
        $header = "===== $name ";
        $start = strpos($report, $header);

        if ($start === false) {
            throw new RuntimeException("Coverage report does not contain $name group");
        }

        $end = strpos($report, "\n===== ", $start + strlen($header));

        if ($end === false) {
            return substr($report, $start);
        }

        return substr($report, $start, $end - $start);
    }
}
