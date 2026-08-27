<?php

namespace TestSuite\Context\Filesystem;

use RuntimeException;
use Stringable;

use function basename;
use function chmod;
use function dirname;
use function touch;

final readonly class File implements Stringable
{
    public string $name;
    public string $directory;

    public function __construct(
        public string $path,
    ) {
        $this->name = basename($path);
        $this->directory = dirname($path);
    }

    public function create(?int $permissions = null): self
    {
        if (!@touch($this->path)) {
            throw new RuntimeException("Cannot create file: $this");
        }

        if ($permissions !== null && !@chmod($this->path, $permissions)) {
            throw new RuntimeException("Cannot change file permissions: $this");
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->path;
    }
}
