<?php

declare(strict_types=1);

namespace PHP\Testing;

final readonly class SourceDiffHunk
{
    public function __construct(
        public int $baseStart,
        public int $baseLines,
        public int $treeStart,
        public int $treeLines
    ) {}
}
