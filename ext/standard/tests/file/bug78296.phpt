--TEST--
Bug #78296 (is_file fails to detect file)
--FILE--
<?php
$dir = Workspace::directory(str_repeat('_', 250));
var_dump(mkdir($dir));
var_dump(is_dir($dir));
?>
--EXPECT--
bool(true)
bool(true)
