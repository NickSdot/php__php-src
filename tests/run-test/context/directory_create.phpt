--TEST--
Directory::create() creates the allocated directory and returns the same object
--FILE--
<?php

$directory = TestPath::directory();

var_dump($directory->create() === $directory);
var_dump(is_dir($directory));

?>
--EXPECT--
bool(true)
bool(true)
