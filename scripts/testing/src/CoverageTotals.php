<?php

declare(strict_types=1);

namespace PHP\Testing;

final class CoverageTotals
{
    private int $baseLines = 0;
    private int $treeLines = 0;
    private int $baseExecutableLines = 0;
    private int $treeExecutableLines = 0;
    private int $baseBranches = 0;
    private int $treeBranches = 0;
    private int $baseExecutableBranches = 0;
    private int $treeExecutableBranches = 0;

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
