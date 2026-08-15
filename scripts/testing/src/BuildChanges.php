<?php

declare(strict_types=1);

namespace PHP\Testing;

use function explode;
use function in_array;
use function str_ends_with;

final readonly class BuildChanges
{
    /** @param list<string> $paths */
    public function __construct(
        private array $paths
    ) {}

    public function onlyTests(): bool
    {
        foreach ($this->paths as $path) {
            if ($this->isTest($path) === false) {
                return false;
            }
        }

        return true;
    }

    /** @return list<string> */
    public function nonTestPaths(): array
    {
        $paths = [];

        foreach ($this->paths as $path) {
            if ($this->isTest($path) === false) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    private function isTest(string $path): bool
    {
        return str_ends_with($path, '.phpt') === true
            || in_array('tests', explode('/', Path::repository($path)), true) === true;
    }
}
