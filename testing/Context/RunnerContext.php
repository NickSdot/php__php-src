<?php

namespace TestSuite\Context;

use LogicException;
use RuntimeException;
use TestContext;
use TestSuite\Context\Filesystem\Remove;

use function array_key_exists;
use function base_convert;
use function basename;
use function crc32;
use function dirname;
use function gethostname;
use function getmypid;
use function is_dir;
use function is_file;
use function mkdir;
use function realpath;
use function sprintf;
use function time;
use function unlink;

require_once __DIR__
    . DIRECTORY_SEPARATOR . 'Filesystem'
    . DIRECTORY_SEPARATOR . 'Remove.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestContext.php';

final class RunnerContext
{
    private static ?self $instance = null;

    private readonly string $id;

    private readonly string $suiteDirectory;

    private int $sequence = 0;

    private array $tests = [];

    private function __construct()
    {
        $this->suiteDirectory = $dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR
            . '.suite';

        $this->id = self::createId();

        if (!@mkdir($this->suiteDirectory, 0777) && !is_dir($this->suiteDirectory)) {
            throw new RuntimeException("Cannot create directory: $this->suiteDirectory");
        }
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function startTest(string $testFile, string $testDirectory, bool $preserve): string
    {
        if (false === $testDirectory = realpath($testDirectory)) {
            throw new RuntimeException("Test directory does not exist: $testFile");
        }

        $this->sequence++;

        $id = basename($testFile)
            . '.'
            . $this->id
            . '-t'
            . $this->sequence;

        $pathSuffix = DIRECTORY_SEPARATOR . $id;

        $paths = [
            'stateFile' => $this->suiteDirectory . $pathSuffix . '.context',
            'relativeDirectory' => $testDirectory . $pathSuffix,
            'workspaceDirectory' => $this->suiteDirectory . $pathSuffix,
        ];

        TestContext::initialize(...$paths);

        $this->tests[$id] = [
            'preserve' => $preserve,
            ...$paths,
        ];

        return $id;
    }

    public function stateFileFor(string $testId): string
    {
        $this->assertTestIsRegistered($testId);

        return $this->tests[$testId]['stateFile'];
    }

    public function finishTest(string $testId): void
    {
        $this->assertTestIsRegistered($testId);

        $test = $this->tests[$testId];

        unset($this->tests[$testId]);

        if ($test['preserve']) {
            return;
        }

        Remove::directory(
            $test['relativeDirectory'],
        );

        Remove::directory(
            $test['workspaceDirectory'],
        );

        if (is_file($test['stateFile'])) {
            unlink($test['stateFile']);
        }
    }

    private function assertTestIsRegistered(string $testId): void
    {
        if (!array_key_exists($testId, $this->tests)) {
            throw new LogicException("Test is not registered: $testId");
        }
    }

    private static function createId(): string
    {
        $host = gethostname();
        $hostId = sprintf('%u', crc32($host === false ? '' : $host));

        return 'r'
            . $hostId
            . '-'
            . getmypid()
            . '-'
            . base_convert((string) time(), 10, 36);
    }
}
