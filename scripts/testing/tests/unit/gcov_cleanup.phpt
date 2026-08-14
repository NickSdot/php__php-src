--TEST--
Gcov coverage resets all data files individually
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

use PHP\Testing\GcovCoverage;
use PHP\Testing\ProcessRunner;
use PHP\Testing\TestTemporaryDirectory;

$temporary = TestTemporaryDirectory::create(
    TestTemporaryDirectory::stateFile('gcov_cleanup')
);

$directory = $temporary->path();

file_put_contents($directory . '/example.gcno', 'notes');
file_put_contents($directory . '/example.gcda', 'data');
file_put_contents($directory . '/other.gcda', 'data');
file_put_contents($directory . '/keep.txt', 'keep');

$coverage = new GcovCoverage($directory, $directory, 'gcov', new ProcessRunner());
$coverage->validateBuild();
$coverage->reset();

var_dump(file_exists($directory . '/example.gcda'));
var_dump(file_exists($directory . '/other.gcda'));
var_dump(file_exists($directory . '/example.gcno'));
var_dump(file_exists($directory . '/keep.txt'));

$temporary->remove();

?>
--CLEAN--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

PHP\Testing\TestTemporaryDirectory::removeFromStateFile(
    PHP\Testing\TestTemporaryDirectory::stateFile('gcov_cleanup')
);

?>
--EXPECT--
bool(false)
bool(false)
bool(true)
bool(true)
