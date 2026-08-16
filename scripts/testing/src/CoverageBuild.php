<?php

declare(strict_types=1);

namespace PHP\Testing;

final readonly class CoverageBuild
{
    public function __construct(
        public ?string $revision,
        public string $sourceDirectory,
        public string $buildDirectory,
        public string $configurationFingerprint
    ) {}
}
