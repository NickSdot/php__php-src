--TEST--
Test Workspace::directory() - named paths - stable names, properties and string conversion
--FILE--
<?php

use TestSuite\Context\TestContext;

$directory = Workspace::directory('fixtures');

var_dump($directory->name, basename($directory->path));
var_dump(dirname($directory->path) === TestContext::instance()->workspaceDirectory);
var_dump((string) $directory === $directory->path);
var_dump(Workspace::directory('fixtures')->path === $directory->path);
var_dump(file_exists($directory));

?>
--EXPECT--
string(8) "fixtures"
string(8) "fixtures"
bool(true)
bool(true)
bool(true)
bool(false)
