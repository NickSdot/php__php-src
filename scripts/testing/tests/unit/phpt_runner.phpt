--TEST--
PHPT runner records the complete suite test count
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

use PHP\Testing\Output;
use PHP\Testing\PhptRunner;
use PHP\Testing\ProcessRunner;
use PHP\Testing\TestTemporaryDirectory;

$temporary = TestTemporaryDirectory::create(
    TestTemporaryDirectory::stateFile('phpt_runner')
);

$directory = $temporary->path();
$runner = "$directory/run-tests.php";

file_put_contents($runner, <<<'PHP'
    <?php
    $resultFile = $argv[array_search('-W', $argv, true) + 1];
    file_put_contents($resultFile, str_repeat("PASS\ttest.phpt\n", 42));
    PHP);

ob_start();
$run = (new PhptRunner(new ProcessRunner(), new Output(), PHP_BINARY, $directory))->run(
    'tree',
    $runner,
    null
);
ob_end_clean();

var_dump($run->testCount);

$temporary->remove();
?>
--CLEAN--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

PHP\Testing\TestTemporaryDirectory::removeFromStateFile(
    PHP\Testing\TestTemporaryDirectory::stateFile('phpt_runner')
);
?>
--EXPECT--
int(42)
