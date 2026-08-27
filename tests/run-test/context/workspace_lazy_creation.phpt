--TEST--
TestContext creates the default workspace only when a path is requested
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.context');
$workspace = dirname($stateFile) . DIRECTORY_SEPARATOR . $id;

var_dump(!is_dir($workspace));

TestPath::file('fixture.txt');

var_dump(is_dir($workspace));

?>
--EXPECT--
bool(true)
bool(true)
