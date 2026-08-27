<?php

namespace TestSuite\Context\Filesystem;

use function is_dir;
use function is_link;
use function rmdir;
use function scandir;
use function unlink;

final class Remove
{
    public static function directory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        foreach (scandir($path, SCANDIR_SORT_NONE) as $name) {

            if ($name === '.' || $name === '..') {
                continue;
            }

            $child = $path . DIRECTORY_SEPARATOR . $name;

            if (is_dir($child) && !is_link($child)) {
                self::directory($child);
            } else {
                unlink($child);
            }
        }

        rmdir($path);
    }
}
