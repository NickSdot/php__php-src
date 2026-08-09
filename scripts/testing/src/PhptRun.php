<?php

declare(strict_types=1);

namespace PHP\Testing;

final readonly class PhptRun
{
    public function __construct(
        public int $status,
        public float $time,
        public ?int $memory
    ) {}

    public function failed(): bool
    {
        return $this->status !== 0;
    }
}
