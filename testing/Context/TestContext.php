<?php

use TestSuite\Context\Filesystem\Path;

final class TestContext
{
    private static ?self $instance = null;

    private array $paths = [];

    public private(set) ?string $workspaceDirectory = null;

    private function __construct(
        public readonly string $id,
        public readonly string $stateFile,
    ) {}

    public static function init(string $testFile): self
    {
        $suiteDirectory = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR
            . '.suite';

        if (!@mkdir($suiteDirectory, 0777) && !is_dir($suiteDirectory)) {
            throw new RuntimeException("Cannot create directory: $suiteDirectory");
        }

        $id = self::createId($testFile);

        $stateFile = $suiteDirectory
            . DIRECTORY_SEPARATOR
            . $id
            . '.state';

        $context = new self($id, $stateFile);
        $context->persist();

        return $context;
    }

    public static function load(string $stateFile): self
    {
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

        return $context;
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

        return self::$instance = self::load($stateFile);
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

        if ($context->workspaceDirectory !== null) {
            throw new LogicException('Workspace directory is already set');
        }

        $context->workspaceDirectory(self::scriptDirectory());
    }

    public static function useSystemWorkspace(): void
    {
        $context = self::instance();

        if ($context->workspaceDirectory !== null) {
            throw new LogicException('Workspace directory is already set');
        }

        $context->workspaceDirectory(sys_get_temp_dir());
    }

    private function workspaceDirectory(?string $root = null): string
    {
        if ($this->workspaceDirectory !== null) {
            return $this->workspaceDirectory;
        }

        $root ??= dirname($this->stateFile);

        $directory = $root
            . DIRECTORY_SEPARATOR
            . basename($this->stateFile, '.state');

        if (!@mkdir($directory) && !is_dir($directory)) {
            throw new RuntimeException("Cannot create directory: $directory");
        }

        $this->workspaceDirectory = $directory;
        $this->persist();

        return $directory;
    }

    private static function createId(string $testFile): string
    {
        if (false === $resolvedTestFile = realpath($testFile)) {
            throw new RuntimeException("Test file does not exist: $testFile");
        }

        return basename($resolvedTestFile)
            . '.h'
            . substr(sha1($resolvedTestFile), 0, 12)
            . '.r'
            . getmypid()
            . '-' . bin2hex(random_bytes(8));
    }

    private static function scriptDirectory(): string
    {
        $scriptFile = $_SERVER['SCRIPT_FILENAME'] ?? null;

        if (!is_string($scriptFile) || $scriptFile === '') {
            throw new RuntimeException('Script file path is not available');
        }

        return dirname($scriptFile);
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
