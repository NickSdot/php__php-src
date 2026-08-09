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
use function sys_get_temp_dir;
use function unlink;

final class TemporaryDirectory
{
    private const PREFIX = 'php-coverage-';
    private const MARKER = '.php-coverage-owner';

    private function __construct(
        private string $path
    ) {}

    public static function create(): self
    {
        $base = realpath(sys_get_temp_dir());

        if ($base === false) {
            throw new RuntimeException('Temporary directory does not exist');
        }

        for ($attempt = 0; $attempt < 10; $attempt++) {

            $directory = $base . '/' . self::PREFIX . bin2hex(random_bytes(8));

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
        $base = realpath(sys_get_temp_dir());
        $real = realpath($this->path);

        if ($base === false
            || $real === false
            || is_link($this->path) === true
            || dirname($real) !== $base
            || str_starts_with(basename($real), self::PREFIX) === false
        ) {
            return false;
        }

        $marker = realpath($real . '/' . self::MARKER);

        if ($marker === false || is_file($marker) === false || is_link($real . '/' . self::MARKER) === true) {
            return false;
        }

        return true;
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
