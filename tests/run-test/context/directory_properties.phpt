--TEST--
Directory exposes its allocated path and name and supports string conversion
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.context');
$workspace = dirname($stateFile) . DIRECTORY_SEPARATOR . $id;
$directory = TestPath::directory('fixtures');

var_dump($directory instanceof \TestSuite\Context\Filesystem\Directory);
var_dump($directory->name);
var_dump($directory->path === $workspace . DIRECTORY_SEPARATOR . 'fixtures');
var_dump((string) $directory === $directory->path);

?>
--EXPECT--
bool(true)
string(8) "fixtures"
bool(true)
bool(true)
