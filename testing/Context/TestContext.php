<?php

use TestSuite\Context\Filesystem\Path;

final class TestContext
{
    private static ?self $instance = null;

    private bool $relative = false;

    private array $paths = [];

    private function __construct(
        private readonly string $stateFile,
        private readonly string $relativeDirectory,
        private readonly string $workspaceDirectory,
    ) {}

    public static function initialize(string $stateFile, string $relativeDirectory, string $workspaceDirectory): void
    {
        if (is_file($stateFile)) {
            throw new LogicException("Context state already exists: $stateFile");
        }

        $context = new self($stateFile, $relativeDirectory, $workspaceDirectory);

        $context->persist();
    }

    public static function allocatePath(?string $name, string $default, ?string $directory = null): string
    {
        Path::validateName($name);

        $context = self::instance();

        if ($directory === null) {
            $directory = $context->workspaceDirectory();
        }

        $path = $directory . DIRECTORY_SEPARATOR . ($name ?? $default);

        if ($name !== null) {
            return $path;
        }

        $count = ($context->paths[$path] ?? 0) + 1;

        $context->paths[$path] = $count;
        $context->persist();

        return "$path-$count";
    }

    public static function useRelativeWorkspace(): void
    {
        $context = self::instance();

        if ($context->relative) {
            return;
        }

        $context->relative = true;
        $context->persist();
    }


    public static function instance(): self
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $stateFile = getenv('PHP_TEST_CONTEXT');

        if ($stateFile === false || $stateFile === '') {
            throw new LogicException('State file path is not available');
        }

        if (!is_file($stateFile)) {
            throw new RuntimeException("State file does not exist: $stateFile");
        }

        $serializedContext = @file_get_contents($stateFile);

        if ($serializedContext === false) {
            throw new RuntimeException("Cannot read context state: $stateFile");
        }

        $context = @unserialize(
            $serializedContext,
            ['allowed_classes' => [self::class]],
        );

        if (!$context instanceof self || $context->stateFile !== $stateFile) {
            throw new RuntimeException("Invalid context state: $stateFile");
        }

        return self::$instance = $context;
    }

    private function workspaceDirectory(): string
    {
        $directory = $this->relative
            ? $this->relativeDirectory
            : $this->workspaceDirectory;

        if (!is_dir($directory) && !@mkdir($directory) && !is_dir($directory)) {
            throw new RuntimeException("Cannot create directory: $directory");
        }

        return $directory;
    }

    private function persist(): void
    {
        $written = @file_put_contents(
            $this->stateFile,
            $context = serialize($this),
            LOCK_EX,
        );

        if ($written !== strlen($context)) {
            throw new RuntimeException("Cannot write state: $this->stateFile");
        }
    }
}
