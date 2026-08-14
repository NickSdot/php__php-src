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

final readonly class CoverageScope
{
    /**
     * @param list<string> $includedPaths
     * @param list<string> $excludedPaths
     */
    private function __construct(
        private bool $global,
        private array $includedPaths = [],
        private array $excludedPaths = []
    ) {}

    public static function global(): self
    {
        return new self(true);
    }

    /**
     * @param list<string> $includedPaths
     * @param list<string> $excludedPaths
     */
    public static function paths(array $includedPaths, array $excludedPaths = []): self
    {
        return new self(false, self::normalisePaths($includedPaths), self::normalisePaths($excludedPaths));
    }

    public function description(): string
    {
        if ($this->global === true) {
            return 'global';
        }

        return implode(', ', $this->includedPaths);
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

        return $this->matchesAnyPath($source, $this->excludedPaths) === false
            && $this->matchesAnyPath($source, $this->includedPaths) === true;
    }

    /** @param list<string> $paths */
    private function matchesAnyPath(string $source, array $paths): bool
    {
        foreach ($paths as $path) {
            if ($this->matchesPath($source, $path) === true) {
                return true;
            }
        }

        return false;
    }

    private function matchesPath(string $source, string $path): bool
    {
        return $source === $path || Path::isDescendantOf($source, $path) === true;
    }

    /** @return list<string> */
    public function sources(CoverageSnapshot $base, CoverageSnapshot $tree): array
    {
        $availableSources = array_unique(array_merge($base->paths(), $tree->paths()));
        sort($availableSources);

        if ($this->global === true) {
            return $availableSources;
        }

        $selectedSources = [];

        foreach ($this->includedPaths as $includedPath) {

            $matchedSource = false;

            foreach ($availableSources as $source) {

                if ($this->matchesPath($source, $includedPath) === false) {
                    continue;
                }

                if ($this->matchesAnyPath($source, $this->excludedPaths) === true) {
                    continue;
                }

                $selectedSources[$source] = true;
                $matchedSource = true;
            }

            if ($matchedSource === false) {
                throw new RuntimeException("Coverage scope was not exercised: $includedPath");
            }
        }

        $sources = array_keys($selectedSources);
        sort($sources);

        return $sources;
    }

    /**
     * @param list<string> $paths
     * @return list<string>
     */
    private static function normalisePaths(array $paths): array
    {
        $normalisedPaths = [];

        foreach ($paths as $path) {
            $normalisedPaths[] = rtrim(Path::repository($path), '/');
        }

        $normalisedPaths = array_unique($normalisedPaths);
        sort($normalisedPaths);

        return $normalisedPaths;
    }
}
