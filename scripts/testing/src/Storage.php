<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function is_dir;
use function is_link;
use function mkdir;
use function realpath;
use function sys_get_temp_dir;

final class Storage
{
    private const string ROOT = 'php-src-quality';

    public static function builds(string $path = ''): string
    {
        return self::directory('builds') . $path;
    }

    public static function locks(string $name = ''): string
    {
        $directory = self::directory('locks');

        if ($name === '') {
            return $directory;
        }

        return "$directory/$name.lock";
    }

    public static function runs(string $path = ''): string
    {
        return self::directory('runs') . $path;
    }

    public static function tests(string $path = ''): string
    {
        return self::directory('tests') . $path;
    }

    private static function directory(string $name): string
    {
        $temporary = realpath(sys_get_temp_dir());

        if ($temporary === false) {
            throw new RuntimeException('System directory does not exist');
        }

        $root = self::create("$temporary/" . self::ROOT);

        return self::create("$root/$name");
    }

    private static function create(string $directory): string
    {
        if (is_link($directory) === true) {
            throw new RuntimeException("Storage directory cannot be a symlink: $directory");
        }

        if (is_dir($directory) === false
            && @mkdir($directory, 0700) === false
            && is_dir($directory) === false
        ) {
            throw new RuntimeException("Could not create storage directory: $directory");
        }

        $real = realpath($directory);

        if ($real === false) {
            throw new RuntimeException("Invalid storage directory: $directory");
        }

        return $real;
    }
}
