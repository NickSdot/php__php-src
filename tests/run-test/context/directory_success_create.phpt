--TEST--
Test Directory::create() - creates a directory and accepts an existing directory
--FILE--
<?php

$directory = Workspace::directory();

var_dump($directory->create() === $directory);
var_dump(is_dir($directory));

$file = $directory->file();
file_put_contents($file, 'preserved');
var_dump($directory->create() === $directory);
var_dump(file_get_contents($file));

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
string(9) "preserved"
