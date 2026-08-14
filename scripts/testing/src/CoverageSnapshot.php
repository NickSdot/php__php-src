<?php

declare(strict_types=1);

namespace PHP\Testing;

use function array_keys;
use function sort;

final class CoverageSnapshot
{
    /** @var array<string, SourceCoverage> */
    private array $sources = [];

    /** @param array<string, SourceCoverage> $sources */
    public function __construct(array $sources = [])
    {
        $this->sources = $sources;
    }

    public function source(string $path): SourceCoverage
    {
        $this->sources[$path] ??= new SourceCoverage();

        return $this->sources[$path];
    }

    public function find(string $path): ?SourceCoverage
    {
        return $this->sources[$path] ?? null;
    }

    public function merge(self $coverage): void
    {
        foreach ($coverage->sources as $path => $source) {
            $this->source($path)->merge($source);
        }
    }

    /** @return list<string> */
    public function paths(): array
    {
        $paths = array_keys($this->sources);
        sort($paths);

        return $paths;
    }

    public function isEmpty(): bool
    {
        return $this->sources === [];
    }
}
