--TEST--
Relative workspaces use the generated CGI script directory
--GET--
context=1
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

var_dump(PHP_SAPI);
var_dump($_GET['context']);
var_dump($file->path === $workspace . DIRECTORY_SEPARATOR . 'relative.txt');

?>
--EXPECT--
string(8) "cgi-fcgi"
string(1) "1"
bool(true)
