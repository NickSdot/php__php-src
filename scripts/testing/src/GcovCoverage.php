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
    private const int BATCH_SIZE = 50;
    private const string DATA_SUFFIX = '.gcda';
    private const string NOTES_SUFFIX = '.gcno';

    public function __construct(
        private CoverageBuild $build,
        private string $gcov,
        private ProcessRunner $process
    ) {}

    public function validateBuild(): void
    {
        if (dirname($this->build->buildDirectory) === $this->build->buildDirectory) {
            throw new RuntimeException('Build directory cannot be filesystem root');
        }

        if ($this->coverageFiles(self::NOTES_SUFFIX) === []) {
            throw new RuntimeException('Coverage build does not contain gcov data: ' . $this->build->buildDirectory);
        }
    }

    public function buildDirectory(): string
    {
        return $this->build->buildDirectory;
    }

    public function reset(): void
    {
        foreach ($this->coverageFiles(self::DATA_SUFFIX) as $file) {
            if (Path::isDescendantOf($file, $this->build->buildDirectory) === false || unlink($file) === false) {
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

        $parser = new GcovParser($this->build->buildDirectory, $this->build->sourceDirectory);

        foreach (array_chunk($dataFiles, self::BATCH_SIZE) as $batch) {

            $report = $this->process->command([$this->gcov, '-b', '-c', '-t', ...$batch], $this->build->buildDirectory);

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
                $this->addCoverageFile($files, $this->build->buildDirectory . "/$relative");
            }

            sort($files);

            return $files;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->build->buildDirectory, FilesystemIterator::SKIP_DOTS));

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
            || Path::isDescendantOf($real, $this->build->buildDirectory) === false
        ) {
            throw new RuntimeException("Coverage file is outside build directory: $path");
        }

        $files[] = str_replace('\\', '/', $real);
    }
}
