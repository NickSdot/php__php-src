<?php

declare(strict_types=1);

namespace PHP\Testing;

final class SourceCoverage
{
    /** @var array<int, true> */
    private array $coveredLines = [];

    /** @var array<int, true> */
    private array $executableLines = [];

    /** @var array<string, true> */
    private array $coveredBranches = [];

    /** @var array<string, true> */
    private array $executableBranches = [];

    /**
     * @param list<int> $coveredLines
     * @param list<int> $executableLines
     * @param list<string> $coveredBranches
     * @param list<string> $executableBranches
     */
    public function __construct(
        array $coveredLines = [],
        array $executableLines = [],
        array $coveredBranches = [],
        array $executableBranches = []
    ) {
        foreach ($coveredLines as $line) {
            $this->coveredLines[$line] = true;
        }

        foreach ($executableLines as $line) {
            $this->executableLines[$line] = true;
        }

        foreach ($coveredBranches as $branch) {
            $this->coveredBranches[$branch] = true;
        }

        foreach ($executableBranches as $branch) {
            $this->executableBranches[$branch] = true;
        }
    }

    public function recordLine(int $line, bool $covered): void
    {
        $this->executableLines[$line] = true;

        if ($covered === true) {
            $this->coveredLines[$line] = true;
        }
    }

    public function recordBranch(string $branch, bool $covered): void
    {
        $this->executableBranches[$branch] = true;

        if ($covered === true) {
            $this->coveredBranches[$branch] = true;
        }
    }

    public function merge(self $coverage): void
    {
        $this->coveredLines += $coverage->coveredLines;
        $this->executableLines += $coverage->executableLines;
        $this->coveredBranches += $coverage->coveredBranches;
        $this->executableBranches += $coverage->executableBranches;
    }

    /** @return array<int, true> */
    public function coveredLines(): array
    {
        return $this->coveredLines;
    }

    /** @return array<int, true> */
    public function executableLines(): array
    {
        return $this->executableLines;
    }

    /** @return array<string, true> */
    public function coveredBranches(): array
    {
        return $this->coveredBranches;
    }

    /** @return array<string, true> */
    public function executableBranches(): array
    {
        return $this->executableBranches;
    }
}
