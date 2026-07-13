<?php

require_once __DIR__ . '/PhptFile.php';
require_once __DIR__ . '/TestStyleFixer.php';

class TestStyleRunner
{
    private string $rootDir;
    /** @var list<class-string<TestStyleFixer>> */
    private array $fixerClasses;

    /**
     * @param list<class-string<TestStyleFixer>> $fixerClasses
     */
    public function __construct(string $rootDir, array $fixerClasses)
    {
        $this->rootDir = $rootDir;
        $this->fixerClasses = $fixerClasses;
    }

    /**
     * @param list<string> $paths
     * @return list<string>
     */
    public function collectFiles(array $paths): array
    {
        if ($paths === []) {
            $paths = [$this->rootDir];
        }

        $files = [];
        foreach ($paths as $path) {
            if (!$this->isAbsolutePath($path)) {
                $path = $this->rootDir . DIRECTORY_SEPARATOR . $path;
            }
            if (is_file($path)) {
                if (str_ends_with($path, '.phpt')) {
                    $files[] = realpath($path) ?: $path;
                }
                continue;
            }
            if (!is_dir($path)) {
                throw new InvalidArgumentException("Path does not exist: $path");
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($iterator as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.phpt')) {
                    $files[] = $file->getPathname();
                }
            }
        }

        sort($files);
        return array_values(array_unique($files));
    }

    /**
     * @param list<string> $files
     * @return array{changed: int, failed: int}
     */
    public function run(array $files, bool $check): array
    {
        $changed = 0;
        $failed = 0;

        foreach ($files as $path) {
            $phpt = new PhptFile($path, $this->rootDir);
            foreach ($this->fixerClasses as $fixerClass) {
                $fixer = new $fixerClass($phpt);
                if (!$fixer->collect()) {
                    continue;
                }

                $changed++;
                echo $phpt->relativePath() . ': ' . $fixer->name() . $this->fixerLocation($fixer);
                if ($check) {
                    echo " needs changes\n";
                    continue;
                }

                if ($fixer->persist()) {
                    echo " fixed\n";
                } else {
                    $failed++;
                    echo " skipped";
                    $reason = $this->fixerFailureReason($fixer);
                    if ($reason !== null) {
                        echo ": $reason";
                    }
                    echo "\n";
                    $phpt->restoreOriginal();
                    $phpt->cleanupArtifacts();
                }
            }
        }

        return ['changed' => $changed, 'failed' => $failed];
    }

    /**
     * @param list<string> $files
     * @return array{changed: bool, failed: bool, output: string, failure: string|null}
     */
    public function print(array $files): array
    {
        if (count($files) !== 1) {
            throw new InvalidArgumentException('--print requires exactly one PHPT file');
        }

        return $this->printFile($files[0]);
    }

    /**
     * @return array{changed: bool, failed: bool, output: string, failure: string|null}
     */
    public function printFile(string $path): array
    {
        $original = file_get_contents($path);
        if ($original === false) {
            throw new RuntimeException("Cannot read $path");
        }

        $phpt = new PhptFile($path, $this->rootDir);
        $changed = false;
        try {
            foreach ($this->fixerClasses as $fixerClass) {
                $fixer = new $fixerClass($phpt);
                if (!$fixer->collect()) {
                    continue;
                }

                $changed = true;
                if (!$fixer->persist()) {
                    return [
                        'changed' => true,
                        'failed' => true,
                        'output' => $original,
                        'failure' => $this->fixerFailureReason($fixer),
                    ];
                }

                $phpt = new PhptFile($path, $this->rootDir);
            }

            $output = file_get_contents($path);
            if ($output === false) {
                throw new RuntimeException("Cannot read rewritten $path");
            }

            return [
                'changed' => $changed,
                'failed' => false,
                'output' => $output,
                'failure' => null,
            ];
        } finally {
            if (file_put_contents($path, $original) === false) {
                throw new RuntimeException("Cannot restore $path");
            }
            $phpt->cleanupArtifacts();
        }
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR)
            || preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1;
    }

    private function fixerLocation(TestStyleFixer $fixer): string
    {
        if (!method_exists($fixer, 'location')) {
            return '';
        }

        $location = $fixer->location();
        return $location === '' ? '' : " ($location)";
    }

    private function fixerFailureReason(TestStyleFixer $fixer): ?string
    {
        if (!method_exists($fixer, 'failureReason')) {
            return null;
        }

        $reason = $fixer->failureReason();
        return $reason === '' ? null : $reason;
    }
}
