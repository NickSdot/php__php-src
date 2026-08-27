<?php

namespace TestSuite\Context\Filesystem;

use Stringable;

use function basename;
use function dirname;

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

    public function __toString(): string
    {
        return $this->path;
    }
}
