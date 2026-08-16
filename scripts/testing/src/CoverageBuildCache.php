<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;
use Throwable;

use function fclose;
use function file_get_contents;
use function file_put_contents;
use function flock;
use function fopen;
use function hash;
use function is_file;
use function is_link;
use function mkdir;
use function trim;
use function unlink;

final class CoverageBuildCache
{
    private const int CACHE_VERSION = 2;
    private const string READY_FILE = '.ready';

    public function __construct(
        private string $key
    ) {}

    public static function key(string $repository, string $role, string $configuration): string
    {
        return hash('sha256', self::CACHE_VERSION . "\0$repository\0$role\0$configuration");
    }

    public function directory(): string
    {
        $lock = fopen($this->lockFile(), 'c+');

        if ($lock === false || flock($lock, LOCK_EX) === false) {
            throw new RuntimeException('Could not lock coverage build cache');
        }

        try {
            $directory = $this->existing();

            if ($directory !== null) {
                return $directory;
            }

            $temporary = TemporaryDirectory::createBuild();

            try {
                if (mkdir($temporary->path() . '/build') === false) {
                    throw new RuntimeException('Could not create coverage build directory');
                }

                if (file_put_contents($this->readyFile($temporary->path()), '') === false
                    || file_put_contents($this->stateFile(), $temporary->path()) === false
                ) {
                    throw new RuntimeException('Could not save coverage build cache');
                }
            } catch (Throwable $throwable) {
                $temporary->remove();
                throw $throwable;
            }

            return $temporary->path();

        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public function remove(): ?string
    {
        $directory = $this->existing();

        if ($directory !== null) {

            $warning = TemporaryDirectory::fromExistingPath($directory)->remove();

            if ($warning !== null) {
                return $warning;
            }
        }

        foreach ([$this->stateFile(), $this->lockFile()] as $file) {
            if (is_file($file) === true && unlink($file) === false) {
                return "Could not delete coverage build cache file: $file";
            }
        }

        return null;
    }

    private function existing(): ?string
    {
        if (is_file($this->stateFile()) === false || is_link($this->stateFile()) === true) {
            return null;
        }

        $contents = file_get_contents($this->stateFile());

        if ($contents === false) {
            throw new RuntimeException('Could not read coverage build cache');
        }

        $readyFile = $this->readyFile(
            $directory = trim($contents)
        );

        $temporary = TemporaryDirectory::fromExistingPath($directory);

        if ($temporary->owned() === true
            && is_file($readyFile) === true
            && is_link($readyFile) === false
        ) {
            return $directory;
        }

        if ($temporary->owned() === true) {
            $temporary->remove();
        }

        if (unlink($this->stateFile()) === false) {
            throw new RuntimeException('Could not reset coverage build cache');
        }

        return null;
    }

    private function stateFile(): string
    {
        return Storage::builds("/{$this->key}.state");
    }

    private function readyFile(string $directory): string
    {
        return "$directory/" . self::READY_FILE;
    }

    private function lockFile(): string
    {
        return Storage::locks("build-$this->key");
    }
}
