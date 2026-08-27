--TEST--
TestContext persists relative workspace selection between SETUP and FILE processes
--SETUP--
<?php

TestContext::useRelativeWorkspace();

?>
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.context');
$workspace = __DIR__ . DIRECTORY_SEPARATOR . $id;
$file = TestPath::file('fixture.txt');

var_dump(is_file($stateFile));
var_dump($file->path === $workspace . DIRECTORY_SEPARATOR . 'fixture.txt');

?>
--EXPECT--
bool(true)
bool(true)
