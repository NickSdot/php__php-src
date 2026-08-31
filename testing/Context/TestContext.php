<?php

namespace TestSuite\Context;

use LogicException;
use RuntimeException;
use TestSuite\Context\Filesystem\Directory;
use TestSuite\Context\Filesystem\File;
use TestSuite\Context\Filesystem\Path;

use function basename;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function getenv;
use function is_file;
use function is_string;
use function mkdir;
use function serialize;
use function strlen;
use function unserialize;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Filesystem' . DIRECTORY_SEPARATOR . 'File.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Filesystem' . DIRECTORY_SEPARATOR . 'Directory.php';

final class TestContext
{
    private static ?self $instance = null;

    public private(set) ?string $workspaceDirectory = null;

    public private(set) array $ini = [];

    public private(set) array $var = [];

    private array $paths = [];

    public private(set) ?string $failureMessage = null;

    private function __construct(
        public readonly string $id,
        public readonly string $stateFile,
    ) {}

    public static function init(string $id, string $stateFile): self
    {
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
            ['allowed_classes' => [self::class, File::class, Directory::class]],
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

    public function allocatePath(?string $name, string $default, ?string $directory = null): string
    {
        Path::validateName($name);

        if ($directory === null) {
            $directory = $this->workspaceDirectory();
        }

        $path = $directory . DIRECTORY_SEPARATOR . ($name ?? $default);

        if ($name !== null) {
            return $path;
        }

        $count = ($this->paths[$path] ?? 0) + 1;

        $this->paths[$path] = $count;
        $this->persist();

        return "$path-$count";
    }

    public function useRelativeWorkspace(): void
    {
        if ($this->workspaceDirectory !== null) {
            throw new LogicException('Workspace directory is already set');
        }

        $scriptFile = $_SERVER['SCRIPT_FILENAME'] ?? null;

        if (!is_string($scriptFile) || $scriptFile === '') {
            throw new RuntimeException('Script file path is not available');
        }

        $this->workspaceDirectory(dirname($scriptFile));
    }

    public function workspaceDirectory(?string $root = null): string
    {
        if ($this->workspaceDirectory !== null) {
            return $this->workspaceDirectory;
        }

        $directory = ($root ?? dirname($this->stateFile))
            . DIRECTORY_SEPARATOR
            . basename($this->stateFile, '.state')
            . '_ws';

        if (!@mkdir($directory)) {
            throw new RuntimeException("Cannot create directory: $directory");
        }

        $this->workspaceDirectory = $directory;
        $this->persist();

        return $directory;
    }

    public function allocateIni(array $ini): void
    {
        $this->ini = $ini;
        $this->persist();
    }

    public function allocateVar(array $var): void
    {
        $this->var = $var;
        $this->persist();
    }

    public function recordFailure(string $message): void
    {
        if ($this->failureMessage !== null) {
            return;
        }

        $this->failureMessage = $message;
        $this->persist();
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
