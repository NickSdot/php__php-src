<?php

declare(strict_types=1);

namespace PHP\Testing;

final readonly class TestTrees
{
    public function __construct(
        public string $base,
        public string $tree,
        public PhptSuites $suites
    ) {}
}
