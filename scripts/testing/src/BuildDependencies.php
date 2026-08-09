<?php

declare(strict_types=1);

namespace PHP\Testing;

use function array_keys;
use function sort;

final readonly class BuildDependencies
{
    /**
     * @param array<string, array<string, true>> $sources
     * @param array<string, array<string, true>> $coverageFiles
     */
    public function __construct(
        private array $sources,
        private array $coverageFiles
    ) {}

    public function merge(self $dependencies): self
    {
        $sources = $this->sources;
        $coverageFiles = $this->coverageFiles;

        foreach ($dependencies->sources as $dependency => $affected) {
            foreach (array_keys($affected) as $source) {
                $sources[$dependency][$source] = true;
            }
        }

        foreach ($dependencies->coverageFiles as $file => $affected) {
            foreach (array_keys($affected) as $source) {
                $coverageFiles[$file][$source] = true;
            }
        }

        return new self($sources, $coverageFiles);
    }

    /**
     * @param list<string> $paths
     * @return list<string>
     */
    public function affectedSources(array $paths): array
    {
        $sources = [];

        foreach ($paths as $path) {
            foreach (array_keys($this->sources[Path::repository($path)] ?? []) as $source) {
                $sources[$source] = true;
            }
        }

        $sources = array_keys($sources);
        sort($sources);

        return $sources;
    }

    /** @return ?list<string> */
    public function coverageFiles(CoverageScope $scope): ?array
    {
        if ($scope->isGlobal() === true) {
            return null;
        }

        $files = [];

        foreach ($this->coverageFiles as $file => $dependencies) {
            foreach (array_keys($dependencies) as $dependency) {
                if ($scope->includes($dependency) === true) {
                    $files[] = $file;
                    break;
                }
            }
        }

        sort($files);

        return $files;
    }
}
