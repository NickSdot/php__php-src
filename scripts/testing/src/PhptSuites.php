<?php

declare(strict_types=1);

namespace PHP\Testing;

final readonly class PhptSuites
{
    /**
     * @param ?list<string> $base
     * @param ?list<string> $tree
     */
    public function __construct(
        public ?array $base,
        public ?array $tree
    ) {}
}
