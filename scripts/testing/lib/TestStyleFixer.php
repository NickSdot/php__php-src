<?php

interface TestStyleFixer
{
    public function name(): string;

    public function collect(): bool;

    public function persist(): bool;
}
