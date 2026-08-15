<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function file_get_contents;
use function is_file;

final readonly class SourceFileChanges
{
    public function __construct(
        private string $baseSource,
        private string $treeSource,
        private GitRepository $repository,
        private ?string $baseBuild = null,
        private ?string $treeBuild = null
    ) {}

    public function source(string $path): SourceDiff
    {
        $base = $this->file($this->baseSource, $this->baseBuild, $path);
        $tree = $this->file($this->treeSource, $this->treeBuild, $path);

        $baseExists = is_file($base);
        $treeExists = is_file($tree);

        if (false === $baseExists && false === $treeExists) {
            return SourceDiff::unchanged();
        }

        if ($treeExists === false) {
            return SourceDiff::deleted();
        }

        if ($baseExists === false) {
            return SourceDiff::added();
        }

        $baseContents = file_get_contents($base);
        $treeContents = file_get_contents($tree);

        if ($baseContents === false || $treeContents === false) {
            throw new RuntimeException("Could not read source: $path");
        }

        if ($baseContents === $treeContents) {
            return SourceDiff::unchanged();
        }

        $changes = SourceDiff::fromPatch($this->repository->diff($base, $tree));

        if ($changes->changed() === false) {
            throw new RuntimeException("Could not map source changes: $path");
        }

        return $changes;
    }

    private function file(string $source, ?string $build, string $path): string
    {
        $file = "$source/$path";

        if (is_file($file) === true || $build === null) {
            return $file;
        }

        return "$build/$path";
    }
}
