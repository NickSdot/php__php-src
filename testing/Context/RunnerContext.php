<?php

namespace TestSuite\Context;

use LogicException;
use RuntimeException;
use TestSuite\Context\Filesystem\Remove;

use function array_key_exists;
use function basename;
use function getmypid;
use function is_dir;
use function is_file;
use function mkdir;
use function realpath;
use function sha1;
use function substr;
use function sys_get_temp_dir;
use function unlink;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Filesystem' . DIRECTORY_SEPARATOR . 'Remove.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestContext.php';

final class RunnerContext
{
    private static ?self $instance = null;

    private readonly string $testStoragePath;

    private array $tests = [];

    private function __construct(
        private readonly bool $preserve,
    ) {
        $storagePath = $this->getStorageRoot();

        if (!@mkdir($storagePath, 0700) && !is_dir($storagePath)) {
            throw new RuntimeException("Cannot create directory: $storagePath");
        }

        $this->testStoragePath = $storagePath;
    }

    public static function instance(bool $preserve): self
    {
        if (self::$instance === null) {
            return self::$instance = new self($preserve);
        }

        if (self::$instance->preserve === $preserve) {
            return self::$instance;
        }

        throw new LogicException('Runner preservation policy cannot change');
    }

    public function startTest(string $testFile): TestContext
    {
        if (false === $resolvedTestFile = realpath($testFile)) {
            throw new RuntimeException("Test file does not exist: $testFile");
        }

        $id = self::createIdFor($resolvedTestFile);

        $stateFile = $this->testStoragePath
            . DIRECTORY_SEPARATOR
            . $id
            . '.' . substr(sha1($resolvedTestFile), 0, 8)
            . '.state';

        $this->cleanupTest($stateFile);

        $context = TestContext::init($id, $stateFile);

        $this->tests[$context->stateFile] = true;

        return $context;
    }

    public function finishTest(TestContext $context): void
    {
        $this->assertTestIsRegistered($context->stateFile);

        unset($this->tests[$context->stateFile]);

        if ($this->preserve) {
            return;
        }

        $this->cleanupTest($context->stateFile);
    }

    private function cleanupTest(string $stateFile): void
    {
        if (!is_file($stateFile)) {
            return;
        }

        $context = TestContext::load($stateFile);

        if ($context->workspaceDirectory !== null) {
            Remove::directory($context->workspaceDirectory);
        }

        if (!@unlink($stateFile)) {
            throw new RuntimeException("Cannot remove state: $stateFile");
        }
    }

    private static function createIdFor(string $testFile): string
    {
        return basename($testFile, '.phpt') . '.' . getmypid();
    }

    private function assertTestIsRegistered(string $stateFile): void
    {
        if (!array_key_exists($stateFile, $this->tests)) {
            throw new LogicException("Test is not registered: $stateFile");
        }
    }

    private function getStorageRoot(): string
    {
        $root = PHP_OS_FAMILY === 'Windows'
            ? sys_get_temp_dir()
            // unix socket paths have small length limits
            : '/tmp';

        return $root . DIRECTORY_SEPARATOR . 'php-src';
    }
}
