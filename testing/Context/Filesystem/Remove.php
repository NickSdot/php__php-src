<?php

namespace TestSuite\Context\Filesystem;

use RuntimeException;

use function chmod;
use function is_dir;
use function is_link;
use function rmdir;
use function scandir;
use function unlink;

final class Remove
{
    public static function directory(string $path): void
    {
        self::removeDirectory($path);
    }

    private static function removeDirectory(string $path, ?string $parent = null): void
    {
        if (!is_dir($path)) {
            return;
        }

        $names = @scandir($path, SCANDIR_SORT_NONE);

        if ($names === false) {
            @chmod($path, 0777);

            $names = @scandir($path, SCANDIR_SORT_NONE);

            if ($names === false) {
                throw new RuntimeException("Cannot read directory: $path");
            }
        }

        foreach ($names as $name) {

            if ($name === '.' || $name === '..') {
                continue;
            }

            $child = $path . DIRECTORY_SEPARATOR . $name;

            if (is_dir($child) && !is_link($child)) {
                self::removeDirectory($child, $path);
            } elseif (!@unlink($child)) {
                @chmod($path, 0777);

                if (!is_link($child)) {
                    @chmod($child, 0666);
                }

                if (!@unlink($child)) {
                    throw new RuntimeException("Cannot remove file: $child");
                }
            }
        }

        if (!@rmdir($path)) {
            @chmod($path, 0777);

            if ($parent !== null) {
                @chmod($parent, 0777);
            }

            if (@rmdir($path)) {
                return;
            }

            throw new RuntimeException("Cannot remove directory: $path");
        }
    }
}
