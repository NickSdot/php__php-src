<?php

namespace TestSuite\Context\Filesystem;

use Stringable;
use TestContext;

use function basename;

final readonly class Directory implements Stringable
{
    public string $name;

    public function __construct(
        public string $path,
    ) {
        $this->name = basename($path);
    }

    public function file(?string $name = null): File
    {
        return new File(TestContext::allocatePath($name, 'file', $this->path));
    }

    public function directory(?string $name = null): self
    {
        return new self(TestContext::allocatePath($name, 'directory', $this->path));
    }

    public function __toString(): string
    {
        return $this->path;
    }
}
