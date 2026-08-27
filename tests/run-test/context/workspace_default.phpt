--TEST--
TestContext allocates paths in its default .suite workspace
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.context');
$workspace = dirname($stateFile) . DIRECTORY_SEPARATOR . $id;
$file = TestPath::file('fixture.txt');

var_dump(
    $workspace
    === getcwd() . DIRECTORY_SEPARATOR . '.suite' . DIRECTORY_SEPARATOR . $id,
);
var_dump($file->path === $workspace . DIRECTORY_SEPARATOR . 'fixture.txt');

?>
--EXPECT--
bool(true)
bool(true)
