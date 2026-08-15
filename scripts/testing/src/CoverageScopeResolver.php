<?php

declare(strict_types=1);

namespace PHP\Testing;

use function array_keys;
use function array_search;
use function array_slice;
use function dirname;
use function explode;
use function implode;
use function is_dir;
use function sort;
use function strlen;
use function substr;

final class CoverageScopeResolver
{
    /** @var list<string> */
    private const array VENDORED_PATHS = [
        'ext/lexbor/lexbor',
        'ext/uri/uriparser',
    ];

    /** @param list<string> $vendoredPaths */
    public function __construct(
        private array $vendoredPaths = self::VENDORED_PATHS
    ) {}

    /** @param list<string> $changedPaths */
    public function resolve(TestCoverageOptions $options, TestTrees $trees, array $changedPaths, ?BuildDependencies $dependencies = null): CoverageScope
    {
        if ($options->global === true) {
            return CoverageScope::global();
        }

        $changedSources = $dependencies?->affectedSources($changedPaths) ?? [];

        if ($options->sources !== []) {
            return CoverageScope::paths($options->sources, $this->excludedPaths($changedSources));
        }

        if ($options->testPaths === []) {
            return CoverageScope::global();
        }

        $components = [];

        foreach ($this->tests($trees) as $test) {

            $component = $this->testComponent($test);

            if ($component === null || $component === '') {
                return CoverageScope::global();
            }

            $components[$component] = true;
        }

        foreach ($changedSources as $source) {

            $component = $this->sourceComponent($source, $trees);

            if ($component === null) {
                return CoverageScope::global();
            }

            $components[$component] = true;
        }

        $components = array_keys($components);
        sort($components);

        if ($components === []) {
            return CoverageScope::global();
        }

        return CoverageScope::paths($components, $this->excludedPaths($changedSources));
    }

    /** @return list<string> */
    private function tests(TestTrees $trees): array
    {
        $tests = [];
        $suites = [
            [$trees->base, $trees->suites->base],
            [$trees->tree, $trees->suites->tree],
        ];

        foreach ($suites as [$root, $suite]) {
            foreach ($suite ?? [] as $test) {
                $tests[substr($test, strlen($root) + 1)] = true;
            }
        }

        $tests = array_keys($tests);
        sort($tests);

        return $tests;
    }

    private function testComponent(string $path): ?string
    {
        $parts = explode('/', $path);
        $tests = array_search('tests', $parts, true);

        if ($tests === false) {
            return null;
        }

        if ($tests === 0) {
            return '';
        }

        return implode('/', array_slice($parts, 0, $tests));
    }

    private function sourceComponent(string $path, TestTrees $trees): ?string
    {
        $parts = explode('/', $path);

        if (array_search('tests', $parts, true) !== false) {
            return null;
        }

        $directory = dirname($path);

        while ($directory !== '.') {

            if ($this->ownsTests($directory, $trees) === true) {
                return $directory;
            }

            $directory = dirname($directory);
        }

        return null;
    }

    private function ownsTests(string $directory, TestTrees $trees): bool
    {
        $tests = $directory === '' ? 'tests' : "$directory/tests";

        return is_dir("{$trees->base}/$tests") === true || is_dir("{$trees->tree}/$tests") === true;
    }

    /**
     * @param list<string> $changedSources
     * @return list<string>
     */
    private function excludedPaths(array $changedSources): array
    {
        $excludedPaths = [];

        foreach ($this->vendoredPaths as $path) {
            if ($this->containsChangedSource($path, $changedSources) === false) {
                $excludedPaths[] = $path;
            }
        }

        return $excludedPaths;
    }

    /** @param list<string> $changedSources */
    private function containsChangedSource(string $path, array $changedSources): bool
    {
        foreach ($changedSources as $source) {
            if ($source === $path || Path::isDescendantOf($source, $path) === true) {
                return true;
            }
        }

        return false;
    }
}
