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

    private function __construct() {}

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function startTest(string $testFile, bool $preserve): string
    {
        $context = TestContext::initialize($testFile);

        $this->tests[$context->id] = [
            'context' => $context,
            'preserve' => $preserve,
        ];

        return $context->id;
    }

    public function stateFileFor(string $testId): string
    {
        $this->assertTestIsRegistered($testId);

        return $this->tests[$testId]['context']->stateFile;
    }

    public function finishTest(string $testId): void
    {
        $this->assertTestIsRegistered($testId);

        $test = $this->tests[$testId];

        unset($this->tests[$testId]);

        if ($test['preserve']) {
            return;
        }

        foreach ($test['context']->workspaceDirectories() as $workspace) {
            Remove::directory($workspace);
        }

        if (is_file($test['context']->stateFile)) {
            unlink($test['context']->stateFile);
        }
    }

    private function assertTestIsRegistered(string $testId): void
    {
        if (!array_key_exists($testId, $this->tests)) {
            throw new LogicException("Test is not registered: $testId");
        }
    }
}
