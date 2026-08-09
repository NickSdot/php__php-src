<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function array_keys;
use function array_merge;
use function array_unique;
use function implode;
use function rtrim;
use function sort;
use function str_starts_with;

final readonly class CoverageScope
{
    /** @param list<string> $paths */
    private function __construct(
        private bool $global,
        private array $paths = []
    ) {}

    public static function global(): self
    {
        return new self(true);
    }

    /** @param list<string> $paths */
    public static function paths(array $paths): self
    {
        $normalised = [];

        foreach ($paths as $path) {
            $normalised[] = rtrim(Path::repository($path), '/');
        }

        $normalised = array_unique($normalised);
        sort($normalised);

        return new self(false, $normalised);
    }

    public function description(): string
    {
        if ($this->global === true) {
            return 'global';
        }

        return implode(', ', $this->paths);
    }

    public function isGlobal(): bool
    {
        return $this->global;
    }

    public function includes(string $source): bool
    {
        if ($this->global === true) {
            return true;
        }

        foreach ($this->paths as $path) {
            if ($source === $path || str_starts_with($source, "$path/") === true) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> */
    public function sources(CoverageSnapshot $base, CoverageSnapshot $tree): array
    {
        $available = array_unique(array_merge($base->paths(), $tree->paths()));
        sort($available);

        if ($this->global === true) {
            return $available;
        }

        $selected = [];

        foreach ($this->paths as $path) {

            $matched = false;

            foreach ($available as $source) {

                if ($source !== $path && str_starts_with($source, "$path/") === false) {
                    continue;
                }

                $selected[$source] = true;
                $matched = true;
            }

            if ($matched === false) {
                throw new RuntimeException("Coverage scope was not exercised: $path");
            }
        }

        $sources = array_keys($selected);
        sort($sources);

        return $sources;
    }
}
