--TEST--
Test context bootstrap - CGI - Workspace is available
--GET--
context=1
--FILE--
<?php

$file = Workspace::file('cgi.txt');

var_dump(PHP_SAPI);
var_dump($_GET['context']);
var_dump($file->name);

?>
--EXPECT--
string(8) "cgi-fcgi"
string(1) "1"
string(7) "cgi.txt"
