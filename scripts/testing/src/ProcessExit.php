<?php

declare(strict_types=1);

namespace PHP\Testing;

final readonly class ProcessExit
{
    public function __construct(
        public int $status,
        public ?int $signal
    ) {}
}
