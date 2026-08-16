<?php

declare(strict_types=1);

namespace PHP\Testing;

final class CoverageComparisonResult
{
    private int $baseSources = 0;
    private int $treeSources = 0;
    private int $baseLines = 0;
    private int $treeLines = 0;
    private int $baseExecutableLines = 0;
    private int $treeExecutableLines = 0;
    private int $baseBranches = 0;
    private int $treeBranches = 0;
    private int $baseExecutableBranches = 0;
    private int $treeExecutableBranches = 0;

    private CoverageLocations $missedLines;
    private CoverageLocations $missedBranches;
    private CoverageLocations $gainedLines;
    private CoverageLocations $gainedBranches;
    private CoverageLocations $uncoveredLines;
    private CoverageLocations $uncoveredBranches;

    public function __construct()
    {
        $this->missedLines = new CoverageLocations();
        $this->missedBranches = new CoverageLocations();
        $this->gainedLines = new CoverageLocations();
        $this->gainedBranches = new CoverageLocations();
        $this->uncoveredLines = new CoverageLocations();
        $this->uncoveredBranches = new CoverageLocations();
    }

    public function baseSources(): int
    {
        return $this->baseSources;
    }

    public function treeSources(): int
    {
        return $this->treeSources;
    }

    public function baseLines(): int
    {
        return $this->baseLines;
    }

    public function treeLines(): int
    {
        return $this->treeLines;
    }

    public function baseExecutableLines(): int
    {
        return $this->baseExecutableLines;
    }

    public function treeExecutableLines(): int
    {
        return $this->treeExecutableLines;
    }

    public function baseBranches(): int
    {
        return $this->baseBranches;
    }

    public function treeBranches(): int
    {
        return $this->treeBranches;
    }

    public function baseExecutableBranches(): int
    {
        return $this->baseExecutableBranches;
    }

    public function treeExecutableBranches(): int
    {
        return $this->treeExecutableBranches;
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

    public function addSources(bool $base, bool $tree): void
    {
        $this->baseSources += (int) $base;
        $this->treeSources += (int) $tree;
    }

    public function addLines(int $base, int $baseExecutable, int $tree, int $treeExecutable): void
    {
        $this->baseLines += $base;
        $this->baseExecutableLines += $baseExecutable;
        $this->treeLines += $tree;
        $this->treeExecutableLines += $treeExecutable;
    }

    public function addBranches(int $base, int $baseExecutable, int $tree, int $treeExecutable): void
    {
        $this->baseBranches += $base;
        $this->baseExecutableBranches += $baseExecutable;
        $this->treeBranches += $tree;
        $this->treeExecutableBranches += $treeExecutable;
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
