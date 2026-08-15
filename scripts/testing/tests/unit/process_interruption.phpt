--TEST--
Interrupted measured processes terminate their children
--SKIPIF--
<?php
if (PHP_OS_FAMILY === 'Windows'
    || function_exists('pcntl_signal') === false
    || function_exists('posix_kill') === false
    || function_exists('posix_setsid') === false
) {
    die('skip process groups are unavailable');
}
?>
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

use PHP\Testing\TestTemporaryDirectory;

$temporary = TestTemporaryDirectory::create(
    TestTemporaryDirectory::stateFile('process_interruption')
);

$directory = $temporary->path();
$autoload = dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
$worker = "$directory/worker.php";
$command = "$directory/command.php";
$runner = "$directory/runner.php";
$heartbeat = "$directory/heartbeat";
$interrupted = "$directory/interrupted";

file_put_contents($worker, <<<'PHP'
<?php
$timeout = microtime(true) + 5;

while (microtime(true) < $timeout) {
    file_put_contents($argv[1], (string) hrtime(true));
    usleep(10_000);
}
PHP);

file_put_contents($command, <<<'PHP'
<?php
$unusedPipes = [];
$worker = proc_open([PHP_BINARY, $argv[1], $argv[2]], [], $unusedPipes);

if (is_resource($worker) === true) {
    proc_close($worker);
}
PHP);

file_put_contents($runner, <<<'PHP'
<?php
require $argv[1];

try {
    (new PHP\Testing\ProcessRunner())->measured(
        [PHP_BINARY, $argv[2], $argv[3], $argv[4]],
        $argv[5],
        getenv(),
        $argv[5] . '/stdout',
        $argv[5] . '/stderr',
        $argv[5] . '/metrics',
    );
} catch (Throwable $throwable) {
    file_put_contents($argv[6], $throwable->getMessage());
}
PHP);

function waitForInterruptionFile(string $file): void
{
    for ($attempt = 0; $attempt < 500; $attempt++) {
        if (file_exists($file) === true) {
            return;
        }

        usleep(10_000);
    }

    throw new RuntimeException("Timed out waiting for file: $file");
}

$unusedPipes = [];
$process = proc_open([
    PHP_BINARY,
    $runner,
    $autoload,
    $command,
    $worker,
    $heartbeat,
    $directory,
    $interrupted,
], [], $unusedPipes);

if (is_resource($process) === false) {
    throw new RuntimeException('Could not start interruption fixture');
}

waitForInterruptionFile($heartbeat);

$status = proc_get_status($process);
posix_kill($status['pid'], SIGINT);
proc_close($process);

waitForInterruptionFile($interrupted);

$before = file_get_contents($heartbeat);
usleep(100_000);

var_dump(str_starts_with(file_get_contents($interrupted), 'Command interrupted:'));
var_dump(file_get_contents($heartbeat) === $before);

$temporary->remove();
?>
--CLEAN--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

PHP\Testing\TestTemporaryDirectory::removeFromStateFile(
    PHP\Testing\TestTemporaryDirectory::stateFile('process_interruption')
);
?>
--EXPECT--
bool(true)
bool(true)
