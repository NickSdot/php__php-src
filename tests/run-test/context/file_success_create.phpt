--TEST--
Test File::create() - creates an empty file without truncating existing contents
--FILE--
<?php

$file = Workspace::file();

var_dump($file->create() === $file);
var_dump(is_file($file), filesize($file));

file_put_contents($file, 'preserved');
var_dump($file->create() === $file);
var_dump(file_get_contents($file));

?>
--EXPECT--
bool(true)
bool(true)
int(0)
bool(true)
string(9) "preserved"
