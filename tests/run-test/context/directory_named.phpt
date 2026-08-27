--TEST--
TestPath::directory() returns a stable explicitly named directory path
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.state');
$workspace = dirname($stateFile) . DIRECTORY_SEPARATOR . $id;
$directory = TestPath::directory('fixtures');

var_dump($directory->path === $workspace . DIRECTORY_SEPARATOR . 'fixtures');
var_dump(TestPath::directory('fixtures')->path === $directory->path);
var_dump(!file_exists($directory->path));

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
