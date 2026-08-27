--TEST--
TestContext can allocate paths in a relative workspace
--SETUP--
<?php

TestContext::useRelativeWorkspace();

?>
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.state');
$workspace = __DIR__ . DIRECTORY_SEPARATOR . $id;
$file = TestPath::file('relative.txt');

var_dump($file->path === $workspace . DIRECTORY_SEPARATOR . 'relative.txt');
var_dump(realpath($workspace) === $workspace);
var_dump(!file_exists($file->path));

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
