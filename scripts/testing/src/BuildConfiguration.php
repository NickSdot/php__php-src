<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function basename;
use function file_get_contents;
use function hash_final;
use function hash_init;
use function hash_update;
use function in_array;
use function str_ends_with;
use function str_starts_with;

final readonly class BuildConfiguration
{
    public function __construct(
        private GitRepository $repository
    ) {}

    public function fingerprint(string $source, string $configuration): string
    {
        $hash = hash_init('sha256');
        $paths = $this->repository->files($source);

        hash_update($hash, $configuration);

        foreach ($paths as $path) {

            if ($this->isInput($path) === false) {
                continue;
            }

            $contents = file_get_contents("$source/$path");

            if ($contents === false) {
                throw new RuntimeException("Could not read build configuration input: $path");
            }

            hash_update($hash, "\0$path\0$contents");
        }

        return hash_final($hash);
    }

    private function isInput(string $path): bool
    {
        $name = basename($path);

        return in_array($name, ['buildconf', 'configure.ac', 'config-stubs'], true) === true
            || str_ends_with($name, '.m4') === true
            || str_ends_with($name, '.w32') === true
            || str_starts_with($name, 'Makefile.') === true;
    }
}
