<?php

namespace TestSuite\Context\Filesystem;

use ValueError;

use function str_contains;

final readonly class Path
{
    public static function validateName(?string $name): void
    {
        if ($name === null) {
            return;
        }

        if ($name === '') {
            throw new ValueError('Name cannot be empty');
        }

        if (str_contains($name, "\0")) {
            throw new ValueError('Name cannot contain null bytes');
        }

        if ($name === '.' || $name === '..') {
            throw new ValueError('Name cannot be . or ..');
        }

        if (str_contains($name, '/') || str_contains($name, '\\')) {
            throw new ValueError('Name cannot contain directory separators');
        }
    }
}
