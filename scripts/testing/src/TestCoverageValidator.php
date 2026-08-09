<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function dirname;
use function getenv;

final class TestCoverageValidator
{
    private const REPORT_FILE = 'coverage.txt';

    public function __construct(
        private Output $output,
        private ProcessRunner $process
    ) {}

    public function validate(TestCoverageOptions $options): int
    {
        $repository = GitRepository::discover(dirname(__DIR__, 3), $this->process);

        $repo = $repository->path();
        $baseRevision = $repository->resolve($options->base);
        $treeRevision = $repository->resolve('HEAD');

        $warning = $repository->behindWarning($options->base);

        if ($warning !== null) {
            $this->output->warning($warning);
        }

        $this->output->printLine('Base: %s (%s)', $options->base, $baseRevision);
        $this->output->printLine('Tree: HEAD (%s)', $treeRevision);

        $temporary = TemporaryDirectory::create();

        try {

            $runtimes = (new CoverageBuilder($repository, $this->process, $this->output, $this->gcov()))->build(
                $options,
                $baseRevision,
                $temporary->path()
            );

            $reporter = new CoverageReporter(
                $runtimes->tree->coverage->buildDirectory() . '/' . self::REPORT_FILE,
                $this->output
            );

            $trees = (new TestTreeBuilder($this->output))->build(
                $runtimes->baseSource,
                $runtimes->treeSource,
                $options->testPaths
            );

            $scope = (new CoverageScopeResolver())->resolve(
                $options,
                $trees,
                $runtimes->changedPaths,
                $runtimes->dependencies()
            );

            $this->output->printLine('Coverage: %s', $scope->description());

            $runs = (new CoverageSuiteRunner($runtimes->base, $runtimes->tree))->run($trees, $scope);

            $comparison = (new CoverageComparator())->compare(
                $runs->base->coverage,
                $runs->tree->coverage,
                $scope,
                new SourceFileChanges(
                    $trees->base,
                    $trees->tree,
                    $repository,
                    $runtimes->base->coverage->buildDirectory(),
                    $runtimes->tree->coverage->buildDirectory()
                )
            );

            return $this->report($reporter, $runtimes->tree->tests, $runs, $comparison);

        } finally {
            $warning = $temporary->remove();

            if ($warning !== null) {
                $this->output->warning($warning);
            }
        }
    }

    private function report(CoverageReporter $reporter, PhptRunner $runner, CoverageRuns $runs, CoverageComparisonResult $comparison): int
    {
        if ($runs->base->tests->failed() === true) {
            $this->output->warning('Base tests failed with selected PHP binary');
        }

        $reporter->report($comparison, $runs->base->tests, $runs->tree->tests);

        if ($runs->tree->tests->failed() === true) {
            throw new RuntimeException('Tree tests failed with selected PHP binary' . $runner->failureOutput('tree'));
        }

        if ($comparison->hasMissedCoverage() === false) {
            $this->output->printLine('PASS');
            return 0;
        }

        $this->output->printLine('FAIL');
        return 1;
    }

    private function gcov(): string
    {
        $gcov = getenv('GCOV');

        if ($gcov === false || $gcov === '') {
            return 'gcov';
        }

        return $gcov;
    }

}
