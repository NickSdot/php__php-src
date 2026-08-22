<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function getenv;
use function sprintf;

final class TestCoverageValidator
{
    private const REPORT_FILE = 'coverage.txt';

    public function __construct(
        private Output $output,
        private ProcessRunner $process
    ) {}

    public function validate(TestCoverageOptions $options): int
    {
        $repository = GitRepository::discover('.', $this->process);

        $baseRevision = $repository->resolve($options->base);
        $treeRevision = $repository->resolve($options->tree ?? 'HEAD');

        $warning = $repository->behindWarning($options->base);

        if ($warning !== null) {
            $this->output->warning($warning);
        }

        $this->output->startProgress($this->header($options, $baseRevision, $treeRevision, $this->initialCoverage($options)));

        $lock = CoverageLock::acquire($repository->commonDirectory());

        try {
            return $this->compare($options, $repository, $baseRevision, $treeRevision);
        } finally {
            $lock->release();
        }
    }

    private function compare(TestCoverageOptions $options, GitRepository $repository, string $baseRevision, string $treeRevision): int
    {
        $temporary = TemporaryDirectory::create();

        try {

            $builder = new CoverageBuilder(
                $repository,
                $this->process,
                $this->output,
                $this->gcov(),
                $options->jobs,
            );

            $runtimes = ($builder)->build(
                $options,
                $baseRevision,
                $treeRevision,
                $temporary->path()
            );

            $reporter = new CoverageReporter(
                $runtimes->tree->coverage->buildDirectory() . '/' . self::REPORT_FILE,
                $this->output
            );

            $trees = (new TestTreeBuilder())->build(
                $runtimes->baseSource,
                $runtimes->treeSource,
                $options->testPaths
            );

            $scope = (new CoverageScopeResolver())->resolve(
                $options,
                $trees,
                $runtimes->treeChangedPaths,
                $runtimes->dependencies()
            );

            $this->output->updateProgressHeader($this->header($options, $baseRevision, $treeRevision, $scope->description()));

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

            $renamedPaths = $repository->renamedPaths(
                $baseRevision,
                $options->tree === null ? null : $treeRevision
            );

            $testChanges = PhptChanges::between(
                $runs->base->tests->results,
                $runs->tree->tests->results,
                $renamedPaths,
                $trees->base,
                $trees->tree
            );

            $this->output->finishProgress();

            return $this->report($reporter, $runtimes->tree->tests, $runs, $comparison, $testChanges);

        } finally {
            $warning = $temporary->remove();

            if ($warning !== null) {
                $this->output->warning($warning);
            }
        }
    }

    private function report(CoverageReporter $reporter, PhptRunner $runner, CoverageRuns $runs, CoverageComparisonResult $comparison, PhptChanges $testChanges): int
    {
        if ($runs->base->tests->failed() === true) {
            $this->output->warning('Base tests failed with selected PHP binary');
        }

        $reporter->report($comparison, $runs->base->tests, $runs->tree->tests, $testChanges);

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

    /** @return list<string> */
    private function header(TestCoverageOptions $options, string $baseRevision, string $treeRevision, ?string $coverage = null): array
    {
        $lines = [];

        if ($coverage !== null) {
            $lines[] = "Coverage: $coverage";
            $lines[] = '';
        }

        $lines[] = sprintf('Base: %s %s', $baseRevision, $options->base);
        $lines[] = sprintf('Tree: %s %s', $treeRevision, $options->tree ?? 'working tree');
        $lines[] = '';

        return $lines;
    }

    private function initialCoverage(TestCoverageOptions $options): ?string
    {
        if ($options->global === true || ($options->sources === [] && $options->testPaths === [])) {
            return 'global';
        }

        if ($options->sources !== []) {
            return CoverageScope::paths($options->sources)->description();
        }

        return null;
    }

}
