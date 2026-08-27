<?php

use TestSuite\Context\Filesystem\Path;
use TestSuite\Context\Workspace;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Workspace.php';

final class TestContext
{
    private static ?self $instance = null;

    private static ?string $scriptDirectory = null;

    private Workspace $workspace = Workspace::Suite;

    private array $workspaceDirectories = [];

    private array $paths = [];

    public readonly string $id;

    private function __construct(
        public readonly string $stateFile,
    ) {
        $this->id = basename($stateFile, '.state');
    }

    public static function initialize(string $testFile): self
    {
        $suiteDirectory = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR
            . '.suite';

        if (!@mkdir($suiteDirectory, 0777) && !is_dir($suiteDirectory)) {
            throw new RuntimeException("Cannot create directory: $suiteDirectory");
        }

        do {
            $id = basename($testFile)
                . '.r'
                . getmypid()
                . '-'
                . strtr(rtrim(base64_encode(random_bytes(6)), '='), '+/', '-_');

            $stateFile = $suiteDirectory
                . DIRECTORY_SEPARATOR
                . $id
                . '.state';

            $state = @fopen($stateFile, 'x+b');
        } while ($state === false && is_file($stateFile));

        if ($state === false) {
            throw new RuntimeException("Cannot create state: $stateFile");
        }

        $context = new self($stateFile);
        $serializedContext = serialize($context);
        $written = @fwrite($state, $serializedContext);

        fclose($state);

        if ($written !== strlen($serializedContext)) {
            @unlink($stateFile);
            throw new RuntimeException("Cannot write state: $stateFile");
        }

        return $context;
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
        self::useWorkspace(Workspace::Relative);
    }

    public static function useSystemWorkspace(): void
    {
        self::useWorkspace(Workspace::System);
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

        self::$scriptDirectory = self::scriptDirectory();

        return self::$instance = self::read($stateFile);
    }

    public function workspaceDirectories(): array
    {
        return array_values(self::read($this->stateFile)->workspaceDirectories);
    }

    private function workspaceDirectory(): string
    {
        $workspaceName = $this->workspace->name;
        $directory = $this->workspaceDirectories[$workspaceName] ?? null;

        if ($directory === null) {
            $directory = match ($this->workspace) {
                Workspace::Suite => dirname($this->stateFile)
                    . DIRECTORY_SEPARATOR
                    . $this->id,
                Workspace::Relative => self::scriptDirectory()
                    . DIRECTORY_SEPARATOR
                    . $this->id,
                Workspace::System => sys_get_temp_dir()
                    . DIRECTORY_SEPARATOR
                    . 'php-test-'
                    . substr(sha1($this->id), 0, 12),
            };
        }

        if (!is_dir($directory) && !@mkdir($directory) && !is_dir($directory)) {
            throw new RuntimeException("Cannot create directory: $directory");
        }

        if (!isset($this->workspaceDirectories[$workspaceName])) {
            $this->workspaceDirectories[$workspaceName] = $directory;
            $this->persist();
        }

        return $directory;
    }

    private static function useWorkspace(Workspace $workspace): void
    {
        $context = self::instance();

        if ($context->workspace === $workspace) {
            return;
        }

        $context->workspace = $workspace;
        $context->persist();
    }

    private static function read(string $stateFile): self
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
            ['allowed_classes' => [self::class, Workspace::class]],
        );

        if (!$context instanceof self || $context->stateFile !== $stateFile) {
            throw new RuntimeException("Invalid context state: $stateFile");
        }

        return $context;
    }

    private static function scriptDirectory(): string
    {
        if (self::$scriptDirectory !== null) {
            return self::$scriptDirectory;
        }

        $scriptFile = $_SERVER['SCRIPT_FILENAME'] ?? null;

        if (!is_string($scriptFile) || $scriptFile === '') {
            throw new RuntimeException('Script file path is not available');
        }

        return self::$scriptDirectory = dirname($scriptFile);
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
