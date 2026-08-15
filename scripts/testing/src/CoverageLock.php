<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function fclose;
use function flock;
use function fopen;
use function hash;
use function is_resource;

final class CoverageLock
{
    /** @param resource $handle */
    private function __construct(
        private mixed $handle
    ) {}

    public static function acquire(string $repository): self
    {
        $file = Storage::locks(hash('sha256', $repository));
        $handle = fopen($file, 'c+');

        if ($handle === false || flock($handle, LOCK_EX) === false) {
            throw new RuntimeException('Could not lock coverage builds');
        }

        return new self($handle);
    }

    public function release(): void
    {
        if (is_resource($this->handle) === false) {
            return;
        }

        flock($this->handle, LOCK_UN);
        fclose($this->handle);
        $this->handle = null;
    }
}
