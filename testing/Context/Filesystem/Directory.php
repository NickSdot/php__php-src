<?php

namespace TestSuite\Context\Filesystem;

use RuntimeException;
use Stringable;
use TestContext;

use function basename;
use function chmod;
use function is_dir;
use function mkdir;

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

    public function create(?int $permissions = null): self
    {
        if (!@mkdir($this->path, $permissions ?? 0777) && !is_dir($this->path)) {
            throw new RuntimeException("Cannot create directory: $this");
        }

        if ($permissions !== null && !@chmod($this->path, $permissions)) {
            throw new RuntimeException("Cannot change directory permissions: $this");
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->path;
    }
}
