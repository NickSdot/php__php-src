<?php

declare(strict_types=1);

namespace PHP\Testing;

final class CoverageTotals
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
}
