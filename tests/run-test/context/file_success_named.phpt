--TEST--
Test Workspace::file() - named paths - stable names, properties and string conversion
--FILE--
<?php

use TestSuite\Context\TestContext;

$file = Workspace::file('fixture.txt');

var_dump($file->name, basename($file->path));
var_dump($file->directory === TestContext::instance()->workspaceDirectory);
var_dump(dirname($file->path) === $file->directory);
var_dump((string) $file === $file->path);
var_dump(Workspace::file('fixture.txt')->path === $file->path);
var_dump(file_exists($file));

?>
--EXPECT--
string(11) "fixture.txt"
string(11) "fixture.txt"
bool(true)
bool(true)
bool(true)
bool(true)
bool(false)
