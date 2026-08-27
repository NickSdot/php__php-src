--TEST--
TestContext can allocate paths in a short system workspace
--SETUP--
<?php

TestContext::useSystemWorkspace();

?>
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.state');
$workspace = sys_get_temp_dir()
    . DIRECTORY_SEPARATOR
    . $id;

var_dump(is_dir($workspace));

$file = TestPath::file('fixture.txt');

var_dump($file->path === $workspace . DIRECTORY_SEPARATOR . 'fixture.txt');
var_dump(is_dir($workspace));
var_dump(dirname($stateFile) === getcwd() . DIRECTORY_SEPARATOR . '.suite');
var_dump(!file_exists($file->path));

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
