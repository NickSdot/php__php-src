--TEST--
File::create() creates an empty anonymously named file
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.state');
$workspace = dirname($stateFile) . DIRECTORY_SEPARATOR . $id;
$file = TestPath::file();

var_dump($file->create() === $file);
var_dump($file->path === $workspace . DIRECTORY_SEPARATOR . 'file-1');
var_dump(is_file($file->path));

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
