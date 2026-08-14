<?php

declare(strict_types=1);

namespace PHP\Testing;

use FilesystemIterator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function array_keys;
use function file_exists;
use function is_dir;
use function is_file;
use function is_link;
use function sort;
use function str_ends_with;

final class PhptScope
{
    /**
     * @param list<string> $paths
     * @return list<string>
     */
    public function files(string $directory, array $paths): array
    {
        $files = [];

        foreach ($paths as $path) {

            $path = Path::repository($path);
            $selected = "$directory/$path";

            if (is_link($selected) === true) {
                throw new InvalidArgumentException("Path must not be a symlink: $path");
            }

            if (is_file($selected) === true) {

                if (str_ends_with($selected, '.phpt') === false) {
                    throw new InvalidArgumentException("Path does not match a PHPT file: $path");
                }

                $files[$selected] = true;
                continue;
            }

            if (is_dir($selected) === false) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($selected, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $entry) {
                if ($entry->isFile() === true && $entry->isLink() === false && $entry->getFilename()[0] !== '.' && str_ends_with($entry->getFilename(), '.phpt') === true) {
                    $files[$entry->getPathname()] = true;
                }
            }
        }

        $files = array_keys($files);
        sort($files);

        return $files;
    }

    /** @param list<string> $paths */
    public function validate(string $baseDirectory, string $treeDirectory, array $paths): void
    {
        foreach ($paths as $path) {

            $path = Path::repository($path);

            if (file_exists("$baseDirectory/$path") === false && file_exists("$treeDirectory/$path") === false) {
                throw new InvalidArgumentException("Path does not match any files: $path");
            }
        }
    }
}
