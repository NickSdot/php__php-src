--TEST--
Coverage lock serialises runs for the same repository
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

use PHP\Testing\TestTemporaryDirectory;

$temporary = TestTemporaryDirectory::create(
    TestTemporaryDirectory::stateFile('coverage_lock')
);

$directory = $temporary->path();
$script = "$directory/lock.php";
$autoload = dirname(__DIR__, 4) . '/scripts/testing/autoload.php';

file_put_contents($script, <<<'PHP'
<?php
require $argv[1];

$lock = PHP\Testing\CoverageLock::acquire($argv[2]);
file_put_contents($argv[3], '');

$timeout = microtime(true) + 5;

while (file_exists($argv[4]) === false && microtime(true) < $timeout) {
    usleep(10_000);
}

$lock->release();
PHP);

function startLock(string $script, string $autoload, string $repository, string $acquired, string $release): mixed
{
    $unusedPipes = [];
    $process = proc_open([PHP_BINARY, $script, $autoload, $repository, $acquired, $release], [], $unusedPipes);

    if (is_resource($process) === false) {
        throw new RuntimeException('Could not start coverage lock process');
    }

    return $process;
}

function waitForFile(string $file): void
{
    for ($attempt = 0; $attempt < 500; $attempt++) {
        if (file_exists($file) === true) {
            return;
        }

        usleep(10_000);
    }

    throw new RuntimeException("Timed out waiting for file: $file");
}

$firstAcquired = "$directory/first-acquired";
$firstRelease = "$directory/first-release";
$secondAcquired = "$directory/second-acquired";
$secondRelease = "$directory/second-release";

$first = startLock($script, $autoload, '/repo', $firstAcquired, $firstRelease);
waitForFile($firstAcquired);

file_put_contents($secondRelease, '');
$second = startLock($script, $autoload, '/repo', $secondAcquired, $secondRelease);

usleep(100_000);
var_dump(file_exists($secondAcquired));

file_put_contents($firstRelease, '');
proc_close($first);
proc_close($second);

var_dump(file_exists($secondAcquired));

$temporary->remove();
?>
--CLEAN--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

PHP\Testing\TestTemporaryDirectory::removeFromStateFile(
    PHP\Testing\TestTemporaryDirectory::stateFile('coverage_lock')
);
?>
--EXPECT--
bool(false)
bool(true)
