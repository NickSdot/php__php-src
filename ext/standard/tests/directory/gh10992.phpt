--TEST--
GH-10992 (Improper long path support for relative paths)
--FILE--
<?php
$workspace = TestPath::directory();
mkdir($workspace);
chdir($workspace);

$dir = TestPath::directory(str_repeat('b', 250 - strlen(getcwd())));
var_dump(mkdir($dir->name));
var_dump(rmdir($dir->name));

$dir = TestPath::directory(str_repeat('b', 265 - strlen(getcwd())));
var_dump(mkdir($dir->name));
var_dump(rmdir($dir->name));
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
