<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function file_get_contents;
use function file_put_contents;
use function hash;
use function is_file;
use function sys_get_temp_dir;
use function unlink;

final class TestTemporaryDirectory
{
    private function __construct(
        private TemporaryDirectory $temporary,
        private string $stateFile
    ) {}

    public static function stateFile(string $name): string
    {
        return sys_get_temp_dir() . '/phpt-' . hash('sha256', __DIR__) . "-$name.state";
    }

    public static function create(string $stateFile): self
    {
        $temporary = TemporaryDirectory::create();

        if (file_put_contents($stateFile, $temporary->path()) === false) {
            $temporary->remove();
            throw new RuntimeException('Could not write state');
        }

        return new self($temporary, $stateFile);
    }

    public static function fromStateFile(string $stateFile): ?self
    {
        if (is_file($stateFile) === false) {
            return null;
        }

        $path = file_get_contents($stateFile);

        if ($path === false) {
            throw new RuntimeException('Could not read state');
        }

        return new self(TemporaryDirectory::fromExistingPath($path), $stateFile);
    }

    public static function removeFromStateFile(string $stateFile): void
    {
        $temporary = self::fromStateFile($stateFile);

        if ($temporary === null) {
            return;
        }

        $temporary->remove();
    }

    public function path(): string
    {
        return $this->temporary->path();
    }

    public function remove(): void
    {
        $warning = $this->temporary->remove();

        if ($warning !== null) {
            throw new RuntimeException($warning);
        }

        if (is_file($this->stateFile) === true && unlink($this->stateFile) === false) {
            throw new RuntimeException('Could not delete state');
        }
    }
}
