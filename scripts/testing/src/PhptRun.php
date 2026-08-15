<?php

declare(strict_types=1);

namespace PHP\Testing;

final readonly class PhptRun
{
    public function __construct(
        public ProcessMeasurement $measurement = new ProcessMeasurement(0, 0.0, 0),
        public PhptResults $results = new PhptResults()
    ) {}

    public function testCount(): int
    {
        return $this->results->count();
    }

    public function failed(): bool
    {
        return $this->measurement->status !== 0;
    }
}
