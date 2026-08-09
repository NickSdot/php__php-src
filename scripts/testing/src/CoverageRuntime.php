<?php

declare(strict_types=1);

namespace PHP\Testing;

final readonly class CoverageRuntime
{
    public function __construct(
        public GcovCoverage $coverage,
        public PhptRunner $tests,
        public BuildDependencies $dependencies
    ) {}
}
