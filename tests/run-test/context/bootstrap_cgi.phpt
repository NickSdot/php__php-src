--TEST--
Context bootstrap makes TestPath available to CGI PHPT sections
--GET--
context=1
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.state');
$workspace = dirname($stateFile) . DIRECTORY_SEPARATOR . $id;
$file = TestPath::file('cgi.txt');

var_dump(PHP_SAPI);
var_dump($_GET['context']);
var_dump($file->path === $workspace . DIRECTORY_SEPARATOR . 'cgi.txt');

?>
--EXPECT--
string(8) "cgi-fcgi"
string(1) "1"
bool(true)
