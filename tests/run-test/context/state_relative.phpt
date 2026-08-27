--TEST--
TestContext keeps its state file in .suite for a relative workspace
--SETUP--
<?php

TestContext::useRelativeWorkspace();

?>
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.context');
$relativeWorkspace = __DIR__ . DIRECTORY_SEPARATOR . $id;

TestPath::file('relative.txt');

var_dump(is_file($stateFile));
var_dump(dirname($stateFile) === getcwd() . DIRECTORY_SEPARATOR . '.suite');
var_dump(!is_file($relativeWorkspace . DIRECTORY_SEPARATOR . '.context'));

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
