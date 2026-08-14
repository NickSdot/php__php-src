<?php

declare(strict_types=1);

namespace PHP\Testing;

use Countable;

use function array_keys;
use function count;
use function ksort;
use function sort;

final class CoverageLocations implements Countable
{
    /** @var array<string, array<int|string, true>> */
    private array $sources = [];

    /** @param array<int|string, true> $entries */
    public function add(string $source, array $entries): void
    {
        foreach (array_keys($entries) as $entry) {
            $this->sources[$source][$entry] = true;
        }
    }

    public function count(): int
    {
        $count = 0;

        foreach ($this->sources as $entries) {
            $count += count($entries);
        }

        return $count;
    }

    public function isEmpty(): bool
    {
        return $this->sources === [];
    }

    /** @return array<string, list<int|string>> */
    public function bySource(): array
    {
        $sources = $this->sources;
        ksort($sources, SORT_NATURAL);

        foreach ($sources as $source => $entries) {
            $entries = array_keys($entries);
            sort($entries, SORT_NATURAL);
            $sources[$source] = $entries;
        }

        return $sources;
    }
}
