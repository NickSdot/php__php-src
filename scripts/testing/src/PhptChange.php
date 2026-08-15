<?php

declare(strict_types=1);

namespace PHP\Testing;

use LogicException;

final readonly class PhptChange
{
    public function __construct(
        public ?string $basePath,
        public ?string $treePath,
        public ?string $baseStatus,
        public ?string $treeStatus
    ) {}

    public function created(): bool
    {
        return $this->basePath === null && $this->treePath !== null;
    }

    public function deleted(): bool
    {
        return $this->basePath !== null && $this->treePath === null;
    }

    public function renamed(): bool
    {
        return $this->basePath !== null
            && $this->treePath !== null
            && $this->basePath !== $this->treePath;
    }

    public function skipped(): bool
    {
        return $this->baseStatus === PhptResults::SKIP || $this->treeStatus === PhptResults::SKIP;
    }

    public function path(): string
    {
        return $this->treePath ?? $this->basePath ?? throw new LogicException('Test change does not contain path');
    }
}
