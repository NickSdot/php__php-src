<?php

declare(strict_types=1);

namespace PHP\Testing;

use InvalidArgumentException;
use RuntimeException;

use function is_executable;
use function is_file;
use function preg_match;
use function preg_replace;
use function realpath;
use function rtrim;
use function str_replace;
use function str_starts_with;

final class Path
{
    public static function repository(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('~^\./~', '', $path);

        if ($path === '' || self::isAbsolute($path) === true || self::hasParentTraversal($path) === true) {
            throw new InvalidArgumentException("Path must be relative to the repo: $path");
        }

        return $path;
    }

    public static function absoluteFile(string $path, string $relativeTo): string
    {
        $real = realpath(
            $absolute = self::absolute($path, $relativeTo)
        );

        if ($real === false || is_file($real) === false || is_executable($real) === false) {
            throw new RuntimeException("Executable not found: $absolute");
        }

        return str_replace('\\', '/', $real);
    }

    public static function absolute(string $path, string $relativeTo): string
    {
        if (self::isAbsolute($path) === true) {
            return $path;
        }

        return "$relativeTo/$path";
    }

    public static function isDescendantOf(string $path, string $directory): bool
    {
        $path = str_replace('\\', '/', $path);
        $directory = rtrim(str_replace('\\', '/', $directory), '/');

        return str_starts_with($path, "$directory/");
    }

    private static function isAbsolute(string $path): bool
    {
        return str_starts_with($path, '/') || preg_match('~^[A-Za-z]:[\\\\/]~', $path) === 1;
    }

    private static function hasParentTraversal(string $path): bool
    {
        return preg_match('~(^|/)\.\.(/|$)~', $path) === 1;
    }
}
