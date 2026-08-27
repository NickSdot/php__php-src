<?php

namespace TestSuite\Context;

use LogicException;
use TestContext;
use TestSuite\Context\Filesystem\Remove;

use function array_key_exists;
use function is_file;
use function unlink;

require_once __DIR__
    . DIRECTORY_SEPARATOR . 'Filesystem'
    . DIRECTORY_SEPARATOR . 'Remove.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestContext.php';

final class RunnerContext
{
    private static ?self $instance = null;

    private array $tests = [];

    private function __construct(
        private readonly bool $preserve,
    ) {}

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
        $context = TestContext::init($testFile);

        $this->tests[$context->id] = true;

        return $context;
    }

    public function finishTest(TestContext $context): void
    {
        $this->assertTestIsRegistered($context->id);

        unset($this->tests[$context->id]);

        if ($this->preserve) {
            return;
        }

        $stateFile = $context->stateFile;
        $context = TestContext::load($stateFile);

        if ($context->workspaceDirectory !== null) {
            Remove::directory($context->workspaceDirectory);
        }

        if (is_file($stateFile)) {
            unlink($stateFile);
        }
    }

    private function assertTestIsRegistered(string $testId): void
    {
        if (!array_key_exists($testId, $this->tests)) {
            throw new LogicException("Test is not registered: $testId");
        }
    }
}
