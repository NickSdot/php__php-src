<?php

declare(strict_types=1);

namespace PHP\Testing;

final readonly class TestCoverageOptions
{
    /**
     * @param list<string> $sources
     * @param list<string> $testPaths
     */
    public function __construct(
        public string $base,
        public array $sources,
        public array $testPaths,
        public bool $global,
        public bool $help
    ) {}
}
