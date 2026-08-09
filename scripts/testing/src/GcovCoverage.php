<?php

declare(strict_types=1);

namespace PHP\Testing;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

use function array_chunk;
use function dirname;
use function is_file;
use function is_link;
use function realpath;
use function sort;
use function str_ends_with;
use function str_replace;
use function unlink;

final class GcovCoverage
{
    private const BATCH_SIZE = 50;
    private const DATA_SUFFIX = '.gcda';
    private const NOTES_SUFFIX = '.gcno';

    public function __construct(
        private string $buildDirectory,
        private string $repo,
        private string $gcov,
        private ProcessRunner $process
    ) {}

    public function validateBuild(): void
    {
        if (dirname($this->buildDirectory) === $this->buildDirectory) {
            throw new RuntimeException('Build directory cannot be filesystem root');
        }

        if ($this->coverageFiles(self::NOTES_SUFFIX) === []) {
            throw new RuntimeException("Coverage build does not contain gcov data: $this->buildDirectory");
        }
    }

    public function buildDirectory(): string
    {
        return $this->buildDirectory;
    }

    /** @param ?list<string> $selected */
    public function reset(?array $selected = null): void
    {
        foreach ($this->coverageFiles(self::DATA_SUFFIX, $selected) as $file) {
            if (Path::isDescendantOf($file, $this->buildDirectory) === false || unlink($file) === false) {
                throw new RuntimeException("Could not delete coverage file: $file");
            }
        }
    }

    /** @param ?list<string> $selected */
    public function readGenerated(?array $selected = null): CoverageSnapshot
    {
        $dataFiles = $this->coverageFiles(self::DATA_SUFFIX, $selected);

        if ($dataFiles === []) {
            throw new RuntimeException('Tests did not generate gcov data');
        }

        $coverage = new CoverageSnapshot();

        $parser = new GcovParser($this->buildDirectory, $this->repo);

        foreach (array_chunk($dataFiles, self::BATCH_SIZE) as $batch) {

            $report = $this->process->command([$this->gcov, '-b', '-c', '-t', ...$batch], $this->buildDirectory);

            $coverage->merge($parser->parse($report));
        }

        if ($coverage->isEmpty() === true) {
            throw new RuntimeException('No gcov coverage reported');
        }

        return $coverage;
    }

    /**
     * @param ?list<string> $selected
     * @return list<string>
     */
    private function coverageFiles(string $suffix, ?array $selected = null): array
    {
        $files = [];

        if ($selected !== null) {
            foreach ($selected as $relative) {
                $this->addCoverageFile($files, "$this->buildDirectory/$relative");
            }

            sort($files);

            return $files;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->buildDirectory, FilesystemIterator::SKIP_DOTS));

        foreach ($iterator as $entry) {

            if (str_ends_with($entry->getFilename(), $suffix) === false) {
                continue;
            }

            $this->addCoverageFile($files, $entry->getPathname());
        }

        sort($files);

        return $files;
    }

    /** @param list<string> $files */
    private function addCoverageFile(array &$files, string $path): void
    {
        if (is_file($path) === false) {
            return;
        }

        $real = realpath($path);

        if (is_link($path) === true
            || $real === false
            || Path::isDescendantOf($real, $this->buildDirectory) === false
        ) {
            throw new RuntimeException("Coverage file is outside build directory: $path");
        }

        $files[] = str_replace('\\', '/', $real);
    }
}
