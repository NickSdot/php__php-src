--TEST--
TestPath::file() allocates separately numbered anonymous file paths
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.context');
$workspace = dirname($stateFile) . DIRECTORY_SEPARATOR . $id;
$first = TestPath::file();
$second = TestPath::file();

var_dump($first->path === $workspace . DIRECTORY_SEPARATOR . 'file-1');
var_dump($second->path === $workspace . DIRECTORY_SEPARATOR . 'file-2');
var_dump(!file_exists($first->path));
var_dump(!file_exists($second->path));

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
