<?php

declare(strict_types=1);

namespace PHP\Testing;

final readonly class ProcessMeasurement
{
    public function __construct(
        public int $status,
        public float $time,
        public ?int $memory
    ) {}
}
