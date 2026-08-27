--TEST--
TestPath::file() returns a stable explicitly named file path
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.state');
$workspace = dirname($stateFile) . DIRECTORY_SEPARATOR . $id;
$file = TestPath::file('fixture.txt');

var_dump($file->path === $workspace . DIRECTORY_SEPARATOR . 'fixture.txt');
var_dump(TestPath::file('fixture.txt')->path === $file->path);
var_dump(!file_exists($file->path));

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
