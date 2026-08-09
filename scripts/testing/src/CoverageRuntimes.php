<?php

declare(strict_types=1);

namespace PHP\Testing;

final readonly class CoverageRuntimes
{
    /** @param list<string> $changedPaths */
    public function __construct(
        public CoverageRuntime $base,
        public CoverageRuntime $tree,
        public string $baseSource,
        public string $treeSource,
        public array $changedPaths
    ) {}

    public function dependencies(): BuildDependencies
    {
        return $this->base->dependencies->merge($this->tree->dependencies);
    }
}
