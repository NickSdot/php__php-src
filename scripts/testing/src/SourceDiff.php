<?php

declare(strict_types=1);

namespace PHP\Testing;

use function explode;
use function preg_match;

final readonly class SourceDiff
{
    /** @param list<SourceDiffHunk> $hunks */
    private function __construct(
        private array $hunks,
        private bool $added = false,
        private bool $deleted = false
    ) {}

    public static function unchanged(): self
    {
        return new self([]);
    }

    public static function added(): self
    {
        return new self([], added: true);
    }

    public static function deleted(): self
    {
        return new self([], deleted: true);
    }

    public static function fromPatch(string $patch): self
    {
        $hunks = [];

        foreach (explode("\n", $patch) as $line) {
            $hunk = self::hunk($line);

            if ($hunk !== null) {
                $hunks[] = $hunk;
            }
        }

        return new self($hunks);
    }

    public function changed(): bool
    {
        return $this->hunks !== [] || $this->added === true || $this->deleted === true;
    }

    public function baseLine(int $treeLine): ?int
    {
        if ($this->added === true) {
            return null;
        }

        $offset = 0;

        foreach ($this->hunks as $hunk) {

            if ($hunk->treeLines === 0) {
                if ($treeLine <= $hunk->treeStart) {
                    return $treeLine + $offset;
                }

                $offset += $hunk->baseLines;
                continue;
            }

            if ($treeLine < $hunk->treeStart) {
                return $treeLine + $offset;
            }

            if ($treeLine < $hunk->treeStart + $hunk->treeLines) {
                return null;
            }

            $offset += $hunk->baseLines - $hunk->treeLines;
        }

        return $treeLine + $offset;
    }

    public function treeLine(int $baseLine): ?int
    {
        if ($this->deleted === true) {
            return null;
        }

        $offset = 0;

        foreach ($this->hunks as $hunk) {

            if ($hunk->baseLines === 0) {

                if ($baseLine <= $hunk->baseStart) {
                    return $baseLine + $offset;
                }

                $offset += $hunk->treeLines;
                continue;
            }

            if ($baseLine < $hunk->baseStart) {
                return $baseLine + $offset;
            }

            if ($baseLine < $hunk->baseStart + $hunk->baseLines) {
                return null;
            }

            $offset += $hunk->treeLines - $hunk->baseLines;
        }

        return $baseLine + $offset;
    }

    private static function hunk(string $line): ?SourceDiffHunk
    {
        if (1 !== preg_match(
            '/^@@ -(\d+)(?:,(\d+))? \+(\d+)(?:,(\d+))? @@/',
            $line,
            $matches,
            PREG_UNMATCHED_AS_NULL
        )) {
            return null;
        }

        return new SourceDiffHunk(
            (int) $matches[1],
            self::length($matches[2]),
            (int) $matches[3],
            self::length($matches[4])
        );
    }

    private static function length(?string $length): int
    {
        if ($length === null) {
            return 1;
        }

        return (int) $length;
    }
}
