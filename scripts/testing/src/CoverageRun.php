<?php

declare(strict_types=1);

namespace PHP\Testing;

final readonly class CoverageRun
{
    public function __construct(
        public PhptRun $tests,
        public CoverageSnapshot $coverage = new CoverageSnapshot()
    ) {}
}
