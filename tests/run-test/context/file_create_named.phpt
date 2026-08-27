--TEST--
File::create() creates an empty explicitly named file
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.state');
$workspace = dirname($stateFile) . DIRECTORY_SEPARATOR . $id;
$file = TestPath::file('touched.txt')->create();

var_dump($file->path === $workspace . DIRECTORY_SEPARATOR . 'touched.txt');
var_dump(is_file($file->path));
var_dump(filesize($file->path));

?>
--EXPECT--
bool(true)
bool(true)
int(0)
