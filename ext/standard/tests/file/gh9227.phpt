--TEST--
Bug GH-9227 (Trailing dots and spaces in filenames are ignored)
--SKIPIF--
<?php
if (PHP_OS_FAMILY !== "Windows") die("skip for Windows only");
?>
--FILE--
<?php
$file = TestPath::file('gh9227.txt');
var_dump(realpath($file . "."));
var_dump(file_put_contents($file, "bar"));
var_dump(realpath($file . "."));
?>
--EXPECT--
bool(false)
int(3)
bool(false)
