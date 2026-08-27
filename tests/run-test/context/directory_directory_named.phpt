--TEST--
Directory::directory() returns a stable explicitly named child directory path
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.state');
$workspace = dirname($stateFile) . DIRECTORY_SEPARATOR . $id;
$directory = TestPath::directory('fixtures');
$child = $directory->directory('nested');
$expected = $workspace
    . DIRECTORY_SEPARATOR
    . 'fixtures'
    . DIRECTORY_SEPARATOR
    . 'nested';

var_dump($child->path === $expected);
var_dump($child->name);
var_dump($directory->directory('nested')->path === $child->path);
var_dump(!file_exists($child->path));

?>
--EXPECT--
bool(true)
string(6) "nested"
bool(true)
bool(true)
