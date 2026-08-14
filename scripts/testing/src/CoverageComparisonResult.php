<?php

declare(strict_types=1);

namespace PHP\Testing;

final class CoverageComparisonResult
{
    private CoverageTotals $totals;
    private CoverageLocations $missedLines;
    private CoverageLocations $missedBranches;
    private CoverageLocations $gainedLines;
    private CoverageLocations $gainedBranches;
    private CoverageLocations $uncoveredLines;
    private CoverageLocations $uncoveredBranches;

    /** @param list<string> $sources */
    public function __construct(
        private readonly array $sources
    ) {
        $this->totals = new CoverageTotals();
        $this->missedLines = new CoverageLocations();
        $this->missedBranches = new CoverageLocations();
        $this->gainedLines = new CoverageLocations();
        $this->gainedBranches = new CoverageLocations();
        $this->uncoveredLines = new CoverageLocations();
        $this->uncoveredBranches = new CoverageLocations();
    }

    /** @return list<string> */
    public function sources(): array
    {
        return $this->sources;
    }

    public function totals(): CoverageTotals
    {
        return $this->totals;
    }

    public function missedLines(): CoverageLocations
    {
        return $this->missedLines;
    }

    public function missedBranches(): CoverageLocations
    {
        return $this->missedBranches;
    }

    public function gainedLines(): CoverageLocations
    {
        return $this->gainedLines;
    }

    public function gainedBranches(): CoverageLocations
    {
        return $this->gainedBranches;
    }

    public function uncoveredLines(): CoverageLocations
    {
        return $this->uncoveredLines;
    }

    public function uncoveredBranches(): CoverageLocations
    {
        return $this->uncoveredBranches;
    }

    public function hasMissedCoverage(): bool
    {
        return $this->missedLines->isEmpty() === false || $this->missedBranches->isEmpty() === false;
    }

    public function addMissedLines(string $source, array $lines): void
    {
        $this->missedLines->add($source, $lines);
    }

    public function addGainedLines(string $source, array $lines): void
    {
        $this->gainedLines->add($source, $lines);
    }

    public function addMissedBranches(string $source, array $branches): void
    {
        $this->missedBranches->add($source, $branches);
    }

    public function addGainedBranches(string $source, array $branches): void
    {
        $this->gainedBranches->add($source, $branches);
    }

    public function addUncoveredLines(string $source, array $lines): void
    {
        $this->uncoveredLines->add($source, $lines);
    }

    public function addUncoveredBranches(string $source, array $branches): void
    {
        $this->uncoveredBranches->add($source, $branches);
    }
}
