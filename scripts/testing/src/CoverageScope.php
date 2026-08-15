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

        foreach ($this->includedPaths as $includedPath) {
            if ($this->includesPath($source, $includedPath) === true) {
                return true;
            }
        }

        return false;
    }

    private function matchesPath(string $source, string $path): bool
    {
        return $source === $path || Path::isDescendantOf($source, $path) === true;
    }

    private function includesPath(string $source, string $includedPath): bool
    {
        if ($this->matchesPath($source, $includedPath) === false) {
            return false;
        }

        foreach ($this->excludedPaths as $excludedPath) {

            if ($this->matchesPath($source, $excludedPath) === false) {
                continue;
            }

            if ($includedPath === $excludedPath || Path::isDescendantOf($includedPath, $excludedPath) === true) {
                continue;
            }

            return false;
        }

        return true;
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

                if ($this->includesPath($source, $includedPath) === false) {
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
