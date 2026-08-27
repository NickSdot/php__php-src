--TEST--
Directory::file() returns a stable explicitly named child file path
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.state');
$workspace = dirname($stateFile) . DIRECTORY_SEPARATOR . $id;
$directory = TestPath::directory('fixtures');
$file = $directory->file('fixture.txt');
$expected = $workspace
    . DIRECTORY_SEPARATOR
    . 'fixtures'
    . DIRECTORY_SEPARATOR
    . 'fixture.txt';

var_dump($file->path === $expected);
var_dump($file->directory === $directory->path);
var_dump($directory->file('fixture.txt')->path === $file->path);
var_dump(!file_exists($file->path));

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
