<?php

declare(strict_types=1);

namespace PHP\Testing;

use function explode;
use function preg_match;
use function realpath;
use function rtrim;
use function str_replace;
use function strlen;
use function substr;
use function trim;

final class GcovParser
{
    public function __construct(
        private string $buildDirectory,
        private string $repo
    ) {}

    public function parse(string $report): CoverageSnapshot
    {
        $lineNumber = 0;
        $sourceCoverage = null;

        $coverage = new CoverageSnapshot();

        foreach (explode("\n", $report) as $line) {

            $candidate = $this->sourcePath($line);

            if ($candidate !== false) {
                $sourceCoverage = $candidate === null ? null : $coverage->source($candidate);
                continue;
            }

            if ($sourceCoverage === null) {
                continue;
            }

            $lineCoverage = $this->lineCoverage($line);

            if ($lineCoverage !== null) {

                [$count, $lineNumber] = $lineCoverage;

                if ($count !== '-') {
                    $sourceCoverage->recordLine($lineNumber, $this->isCovered($count));
                }

                continue;
            }

            $branchCoverage = $this->branchCoverage($line);

            if ($branchCoverage !== null) {

                [$branch, $taken] = $branchCoverage;

                $sourceCoverage->recordBranch("$lineNumber:$branch", $taken > 0);

            }
        }

        return $coverage;
    }

    private function sourcePath(string $line): string|null|false
    {
        if (preg_match('/^\s*-:\s*0:Source:(.*)$/', $line, $matches) !== 1) {
            return false;
        }

        return $this->normaliseSource(trim($matches[1]));
    }

    /** @return ?array{string, int} */
    private function lineCoverage(string $line): ?array
    {
        if (preg_match('/^\s*([^:]+):\s*(\d+):/', $line, $matches) !== 1) {
            return null;
        }

        return [trim($matches[1]), (int) $matches[2]];
    }

    /** @return ?array{string, int} */
    private function branchCoverage(string $line): ?array
    {
        if (preg_match('/^branch\s+(\d+)\s+(taken\s+(\d+)|never executed)/', $line, $matches) !== 1) {
            return null;
        }

        return [$matches[1], (int) ($matches[3] ?? 0)];
    }

    private function isCovered(string $count): bool
    {
        return preg_match('/^\d/', $count) === 1;
    }

    private function normaliseSource(string $source): ?string
    {
        $source = str_replace('\\', '/', $source);

        foreach ([$this->buildDirectory, $this->repo] as $root) {

            $root = rtrim(str_replace('\\', '/', $root), '/');
            $path = realpath(Path::absolute($source, $root));

            if ($path !== false && Path::isDescendantOf($path, $root) === true) {
                return substr(str_replace('\\', '/', $path), strlen($root) + 1);
            }
        }

        return null;
    }

}
