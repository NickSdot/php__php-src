<?php

declare(strict_types=1);

namespace PHP\Testing;

final readonly class CoverageRuns
{
    public function __construct(
        public CoverageRun $base,
        public CoverageRun $tree
    ) {}
}
