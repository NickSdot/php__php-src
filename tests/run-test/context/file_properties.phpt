--TEST--
File exposes its allocated path, name, and parent directory and supports string conversion
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.state');
$workspace = dirname($stateFile) . DIRECTORY_SEPARATOR . $id;
$file = TestPath::file('fixture.txt');

var_dump($file instanceof \TestSuite\Context\Filesystem\File);
var_dump($file->name);
var_dump($file->directory === $workspace);
var_dump($file->path === $workspace . DIRECTORY_SEPARATOR . 'fixture.txt');
var_dump((string) $file === $file->path);

?>
--EXPECT--
bool(true)
string(11) "fixture.txt"
bool(true)
bool(true)
bool(true)
