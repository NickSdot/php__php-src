<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function array_diff_key;
use function count;
use function explode;

final class CoverageComparator
{
    public function compare(CoverageSnapshot $base, CoverageSnapshot $tree, ?CoverageScope $scope = null, ?SourceFileChanges $changes = null): CoverageComparisonResult
    {
        $sources = ($scope ?? CoverageScope::global())->sources($base, $tree);
        $result = new CoverageComparisonResult($sources);

        foreach ($sources as $source) {
            $this->compareSource($result, $source, $base->find($source), $tree->find($source), $changes?->source($source));
        }

        return $result;
    }

    private function compareSource(CoverageComparisonResult $result, string $source, ?SourceCoverage $base, ?SourceCoverage $tree, ?SourceDiff $changes): void
    {
        $both = $base !== null && $tree !== null;

        $totals = $result->totals();
        $totals->addSources($base !== null, $tree !== null);

        $base ??= new SourceCoverage();
        $tree ??= new SourceCoverage();

        if ($changes !== null && $changes->changed() === true) {
            $this->compareChangedSource($result, $source, $base, $tree, $changes);
        } else {
            $this->compareMatchedSource($result, $source, $base, $tree, $both);
        }

        $totals->addLines(
            count($base->coveredLines()),
            count($base->executableLines()),
            count($tree->coveredLines()),
            count($tree->executableLines())
        );

        $totals->addBranches(
            count($base->coveredBranches()),
            count($base->executableBranches()),
            count($tree->coveredBranches()),
            count($tree->executableBranches())
        );
    }

    private function compareMatchedSource(CoverageComparisonResult $result, string $source, SourceCoverage $base, SourceCoverage $tree, bool $validate): void
    {
        if ($validate === true) {
            $this->validateLocations($source, $base, $tree);
        }

        $result->addGainedLines($source, array_diff_key($tree->coveredLines(), $base->coveredLines()));
        $result->addMissedLines($source, array_diff_key($base->coveredLines(), $tree->coveredLines()));

        $result->addGainedBranches(
            $source,
            array_diff_key($tree->coveredBranches(), $base->coveredBranches())
        );

        $result->addMissedBranches(
            $source,
            array_diff_key($base->coveredBranches(), $tree->coveredBranches())
        );

        $result->addUncoveredLines(
            $source,
            array_diff_key($tree->executableLines(), $tree->coveredLines(), $base->coveredLines())
        );

        $result->addUncoveredBranches(
            $source,
            array_diff_key($tree->executableBranches(), $tree->coveredBranches(), $base->coveredBranches())
        );
    }

    private function compareChangedSource(CoverageComparisonResult $result, string $source, SourceCoverage $base, SourceCoverage $tree, SourceDiff $changes): void
    {
        $this->compareChangedLines($result, $source, $base, $tree, $changes);
        $this->compareChangedBranches($result, $source, $base, $tree, $changes);
    }

    private function compareChangedLines(CoverageComparisonResult $result, string $source, SourceCoverage $base, SourceCoverage $tree, SourceDiff $changes): void
    {
        foreach ($tree->executableLines() as $line => $_) {

            $baseLine = $changes->baseLine($line);
            $comparable = $baseLine !== null && isset($base->executableLines()[$baseLine]);
            $baseCovered = $comparable === true && isset($base->coveredLines()[$baseLine]);
            $treeCovered = isset($tree->coveredLines()[$line]);

            if ($treeCovered === true) {

                if ($comparable === false || $baseCovered === false) {
                    $result->addGainedLines($source, [$line => true]);
                }

                continue;
            }

            if ($comparable === false || $baseCovered === true) {
                $result->addMissedLines($source, [$line => true]);
                continue;
            }

            $result->addUncoveredLines($source, [$line => true]);
        }

        foreach ($base->coveredLines() as $baseLine => $_) {

            $treeLine = $changes->treeLine($baseLine);

            if ($treeLine !== null && isset($tree->executableLines()[$treeLine]) === false) {
                $result->addMissedLines($source, [$treeLine => true]);
            }
        }
    }

    private function compareChangedBranches(CoverageComparisonResult $result, string $source, SourceCoverage $base, SourceCoverage $tree, SourceDiff $changes): void
    {
        foreach ($tree->executableBranches() as $branch => $_) {

            [$line, $outcome] = explode(':', $branch, 2);
            $baseLine = $changes->baseLine((int) $line);
            $baseBranch = "$baseLine:$outcome";
            $comparable = $baseLine !== null && isset($base->executableBranches()[$baseBranch]);
            $baseCovered = $comparable === true && isset($base->coveredBranches()[$baseBranch]);
            $treeCovered = isset($tree->coveredBranches()[$branch]);

            if ($treeCovered === true) {

                if ($comparable === false || $baseCovered === false) {
                    $result->addGainedBranches($source, [$branch => true]);
                }

                continue;
            }

            if ($comparable === false || $baseCovered === true) {
                $result->addMissedBranches($source, [$branch => true]);
                continue;
            }

            $result->addUncoveredBranches($source, [$branch => true]);
        }

        foreach ($base->coveredBranches() as $baseBranch => $_) {

            [$baseLine, $outcome] = explode(':', $baseBranch, 2);
            $treeLine = $changes->treeLine((int) $baseLine);

            if ($treeLine === null) {
                continue;
            }

            $treeBranch = "$treeLine:$outcome";

            if (isset($tree->executableBranches()[$treeBranch]) === false) {
                $result->addMissedBranches($source, [$treeBranch => true]);
            }
        }
    }

    private function validateLocations(string $source, SourceCoverage $base, SourceCoverage $tree): void
    {
        if ($this->sameKeys($base->executableLines(), $tree->executableLines()) === false) {
            throw new RuntimeException("Line coverage map changed: $source");
        }

        if ($this->sameKeys($base->executableBranches(), $tree->executableBranches()) === false) {
            throw new RuntimeException("Branch coverage map changed: $source");
        }
    }

    /**
     * @param array<int|string, true> $first
     * @param array<int|string, true> $second
     */
    private function sameKeys(array $first, array $second): bool
    {
        return array_diff_key($first, $second) === [] && array_diff_key($second, $first) === [];
    }
}
