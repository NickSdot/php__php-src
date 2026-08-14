<?php

declare(strict_types=1);

namespace PHP\Testing;

use function array_keys;
use function in_array;

final class CoverageComparisonResult
{
    private CoverageTotals $totals;

    /** @var list<string> */
    private array $missedLines = [];

    /** @var list<string> */
    private array $missedBranches = [];

    /** @var list<string> */
    private array $gainedLines = [];

    /** @var list<string> */
    private array $gainedBranches = [];

    /** @var list<string> */
    private array $uncoveredLines = [];

    /** @var list<string> */
    private array $uncoveredBranches = [];

    /** @param list<string> $sources */
    public function __construct(
        private readonly array $sources
    ) {
        $this->totals = new CoverageTotals();
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

    /** @return list<string> */
    public function missedLines(): array
    {
        return $this->missedLines;
    }

    /** @return list<string> */
    public function missedBranches(): array
    {
        return $this->missedBranches;
    }

    /** @return list<string> */
    public function gainedLines(): array
    {
        return $this->gainedLines;
    }

    /** @return list<string> */
    public function gainedBranches(): array
    {
        return $this->gainedBranches;
    }

    /** @return list<string> */
    public function uncoveredLines(): array
    {
        return $this->uncoveredLines;
    }

    /** @return list<string> */
    public function uncoveredBranches(): array
    {
        return $this->uncoveredBranches;
    }

    public function hasMissedCoverage(): bool
    {
        return $this->missedLines !== [] || $this->missedBranches !== [];
    }

    public function addMissedLines(string $source, array $lines): void
    {
        $this->addLocations($this->missedLines, $source, $lines);
    }

    public function addGainedLines(string $source, array $lines): void
    {
        $this->addLocations($this->gainedLines, $source, $lines);
    }

    public function addMissedBranches(string $source, array $branches): void
    {
        $this->addLocations($this->missedBranches, $source, $branches);
    }

    public function addGainedBranches(string $source, array $branches): void
    {
        $this->addLocations($this->gainedBranches, $source, $branches);
    }

    public function addUncoveredLines(string $source, array $lines): void
    {
        $this->addLocations($this->uncoveredLines, $source, $lines);
    }

    public function addUncoveredBranches(string $source, array $branches): void
    {
        $this->addLocations($this->uncoveredBranches, $source, $branches);
    }

    /**
     * @param list<string> $locations
     * @param array<int|string, true> $entries
     */
    private function addLocations(array &$locations, string $source, array $entries): void
    {
        foreach (array_keys($entries) as $entry) {

            $location = "$source:$entry";

            if (in_array($location, $locations, true) === false) {
                $locations[] = $location;
            }
        }
    }
}
