<?php

declare(strict_types=1);

namespace PHP\Testing;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

use function file_get_contents;
use function file_put_contents;
use function hash;
use function is_array;
use function is_file;
use function json_decode;
use function json_encode;
use function realpath;
use function rtrim;
use function sort;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function str_split;
use function strlen;
use function strpos;
use function substr;

final class BuildDependencyReader
{
    private const string CACHE_FILE = '.deps';

    public function read(CoverageBuild $build): BuildDependencies
    {
        $buildDirectory = $build->buildDirectory;
        $sourceDirectory = $build->sourceDirectory;

        $files = $this->files($buildDirectory);
        $fingerprint = $this->fingerprint($files, $buildDirectory, $sourceDirectory);
        $cached = $this->cached($buildDirectory, $fingerprint);

        if ($cached !== null) {
            return $cached;
        }

        $sources = [];
        $coverageFiles = [];

        foreach ($files as $file) {

            $contents = file_get_contents($file);

            if ($contents === false) {
                throw new RuntimeException('Could not read build dependencies');
            }

            $dependencies = $this->prerequisites($contents);
            $source = $this->relative($dependencies[0] ?? '', $buildDirectory, $sourceDirectory);

            if ($source === null) {
                continue;
            }

            foreach ($dependencies as $dependency) {

                $dependency = $this->relative($dependency, $buildDirectory, $sourceDirectory);

                if ($dependency !== null) {
                    $sources[$dependency][$source] = true;
                    $coverageFiles[$this->dataFile($file, $buildDirectory)][$dependency] = true;
                }
            }
        }

        $this->save($buildDirectory, $fingerprint, $sources, $coverageFiles);

        return new BuildDependencies($sources, $coverageFiles);
    }

    /** @return list<string> */
    private function files(string $buildDirectory): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($buildDirectory, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $entry) {
            if ($entry->isFile() === true && str_ends_with($entry->getFilename(), '.dep') === true) {
                $files[] = $entry->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    /** @param list<string> $files */
    private function fingerprint(array $files, string $buildDirectory, string $sourceDirectory): string
    {
        $metadata = $sourceDirectory;

        foreach ($files as $file) {

            $contents = file_get_contents($file);

            if ($contents === false) {
                throw new RuntimeException('Could not read build dependencies');
            }

            $metadata .= "\0" . $this->dataFile($file, $buildDirectory) . "\0$contents";
        }

        return hash('sha256', $metadata);
    }

    private function cached(string $buildDirectory, string $fingerprint): ?BuildDependencies
    {
        $file = "$buildDirectory/" . self::CACHE_FILE;

        if (is_file($file) === false) {
            return null;
        }

        $contents = file_get_contents($file);

        if ($contents === false) {
            return null;
        }

        $cache = json_decode($contents, true);

        if (is_array($cache) === false
            || ($cache['fingerprint'] ?? null) !== $fingerprint
            || is_array($cache['sources'] ?? null) === false
            || is_array($cache['coverageFiles'] ?? null) === false
        ) {
            return null;
        }

        return new BuildDependencies($cache['sources'], $cache['coverageFiles']);
    }

    /**
     * @param array<string, array<string, true>> $sources
     * @param array<string, array<string, true>> $coverageFiles
     */
    private function save(string $buildDirectory, string $fingerprint, array $sources, array $coverageFiles): void
    {
        $contents = json_encode([
            'fingerprint' => $fingerprint,
            'sources' => $sources,
            'coverageFiles' => $coverageFiles,
        ]);

        if ($contents === false || file_put_contents("$buildDirectory/" . self::CACHE_FILE, $contents) === false) {
            throw new RuntimeException('Could not cache build dependencies');
        }
    }

    private function dataFile(string $dependencyFile, string $buildDirectory): string
    {
        $dependencyFile = str_replace('\\', '/', $dependencyFile);
        $buildDirectory = rtrim(str_replace('\\', '/', $buildDirectory), '/');

        return substr($dependencyFile, strlen($buildDirectory) + 1, -4) . '.gcda';
    }

    /** @return list<string> */
    private function prerequisites(string $contents): array
    {
        $separator = strpos($contents, ':');

        if ($separator === false) {
            return [];
        }

        $word = '';
        $words = [];
        $escaped = false;

        foreach (str_split(substr($contents, $separator + 1)) as $character) {

            if ($escaped === true) {
                $escaped = false;

                if ($character !== "\n" && $character !== "\r") {
                    $word .= $character;
                }

                continue;
            }

            if ($character === '\\') {
                $escaped = true;
                continue;
            }

            if ($character === ' ' || $character === "\t" || $character === "\n" || $character === "\r") {

                if ($word !== '') {
                    $words[] = $word;
                    $word = '';
                }

                continue;
            }

            $word .= $character;
        }

        if ($word !== '') {
            $words[] = $word;
        }

        return $words;
    }

    private function relative(string $path, string $buildDirectory, string $sourceDirectory): ?string
    {
        if ($path === '') {
            return null;
        }

        $path = str_replace('\\', '/', $path);
        $buildDirectory = rtrim(str_replace('\\', '/', $buildDirectory), '/');
        $sourceDirectory = rtrim(str_replace('\\', '/', $sourceDirectory), '/');

        foreach ([$path, "$buildDirectory/$path", "$sourceDirectory/$path"] as $candidate) {

            $real = realpath($candidate);

            if ($real === false || is_file($real) === false) {
                continue;
            }

            $real = str_replace('\\', '/', $real);

            foreach ([$sourceDirectory, $buildDirectory] as $root) {
                if (str_starts_with($real, "$root/") === true) {
                    return Path::repository(substr($real, strlen($root) + 1));
                }
            }
        }

        return null;
    }
}
