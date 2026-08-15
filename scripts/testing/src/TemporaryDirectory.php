<?php

declare(strict_types=1);

namespace PHP\Testing;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

use function basename;
use function bin2hex;
use function dirname;
use function file_put_contents;
use function is_file;
use function is_link;
use function mkdir;
use function random_bytes;
use function realpath;
use function rmdir;
use function str_starts_with;
use function unlink;

final class TemporaryDirectory
{
    private const string BUILD_PREFIX = 'build-';
    private const string MARKER = '.owner';
    private const string RUN_PREFIX = 'run-';

    private function __construct(
        private string $path
    ) {}

    public static function create(): self
    {
        return self::createIn(Storage::runs(), self::RUN_PREFIX);
    }

    public static function createBuild(): self
    {
        return self::createIn(Storage::builds(), self::BUILD_PREFIX);
    }

    private static function createIn(string $base, string $prefix): self
    {

        for ($attempt = 0; $attempt < 10; $attempt++) {

            $directory = "$base/$prefix" . bin2hex(random_bytes(8));

            if (@mkdir($directory, 0700) === false) {
                continue;
            }

            if (file_put_contents($directory . '/' . self::MARKER, '') !== false) {
                return new self($directory);
            }

            rmdir($directory);
            throw new RuntimeException('Could not initialise temporary directory');
        }

        throw new RuntimeException('Could not create temporary directory');
    }

    public static function fromExistingPath(string $path): self
    {
        return new self($path);
    }

    public function path(): string
    {
        return $this->path;
    }

    public function owned(): bool
    {
        $real = realpath($this->path);

        if ($real === false
            || is_link($this->path) === true
            || $this->isManagedPath($real) === false
        ) {
            return false;
        }

        $marker = realpath($real . '/' . self::MARKER);

        if ($marker === false || is_file($marker) === false || is_link($real . '/' . self::MARKER) === true) {
            return false;
        }

        return true;
    }

    private function isManagedPath(string $path): bool
    {
        $name = basename($path);
        $parent = dirname($path);

        return ($parent === Storage::runs() && str_starts_with($name, self::RUN_PREFIX) === true)
            || ($parent === Storage::builds() && str_starts_with($name, self::BUILD_PREFIX) === true);
    }

    public function remove(): ?string
    {
        if ($this->owned() === false) {
            return "Could not delete temporary directory: $this->path";
        }

        $real = realpath($this->path);
        $marker = realpath($real . '/' . self::MARKER);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($real, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $entry) {

            if ($entry->getRealPath() === $marker) {
                continue;
            }

            if ($entry->isDir() === true && $entry->isLink() === false) {
                if (@rmdir($entry->getPathname()) === false) {
                    return "Could not delete temporary directory: $this->path";
                }

                continue;
            }

            if (@unlink($entry->getPathname()) === false) {
                return "Could not delete temporary directory: $this->path";
            }
        }

        if (@unlink($marker) === false) {
            return "Could not delete temporary directory: $this->path";
        }

        if (@rmdir($real) === false) {
            file_put_contents($marker, '');
            return "Could not delete temporary directory: $this->path";
        }

        return null;
    }
}
