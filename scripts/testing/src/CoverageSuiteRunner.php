<?php

declare(strict_types=1);

namespace PHP\Testing;

final class CoverageSuiteRunner
{
    public function __construct(
        private CoverageRuntime $base,
        private CoverageRuntime $tree
    ) {}

    public function run(TestTrees $trees, CoverageScope $scope): CoverageRuns
    {
        $baseFiles = $this->base->dependencies->coverageFiles($scope);
        $treeFiles = $this->tree->dependencies->coverageFiles($scope);

        try {
            return new CoverageRuns(
                $this->runSuite('base', $trees->base, $trees->suites->base, $this->base, $baseFiles),
                $this->runSuite('tree', $trees->tree, $trees->suites->tree, $this->tree, $treeFiles),
            );
        } finally {

            $this->base->coverage->reset($baseFiles);

            if ($this->tree->coverage !== $this->base->coverage) {
                $this->tree->coverage->reset($treeFiles);
            }
        }
    }

    /**
     * @param ?list<string> $tests
     * @param ?list<string> $coverageFiles
     */
    private function runSuite(string $name, string $tree, ?array $tests, CoverageRuntime $runtime, ?array $coverageFiles): CoverageRun
    {
        $runtime->coverage->reset($coverageFiles);

        $run = $runtime->tests->run($name, "$tree/run-tests.php", $tests);

        if ($tests === []) {
            return new CoverageRun($run);
        }

        return new CoverageRun($run, $runtime->coverage->readGenerated($coverageFiles));
    }
}
