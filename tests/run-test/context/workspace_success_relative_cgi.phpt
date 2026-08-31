--TEST--
Test TestContext workspace - relative location - beside the generated CGI script
--GET--
context=1
--SETUP--
<?php

Workspace::useRelative();

?>
--FILE--
<?php

$file = Workspace::file();

var_dump(PHP_SAPI);
var_dump($_GET['context']);
var_dump(realpath(dirname($file->directory)) === __DIR__);

?>
--EXPECT--
string(8) "cgi-fcgi"
string(1) "1"
bool(true)
