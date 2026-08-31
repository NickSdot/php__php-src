--TEST--
Test Workspace::directory() - anonymous paths - distinct names without creating directories
--FILE--
<?php

use TestSuite\Context\TestContext;

$first = Workspace::directory();
$second = Workspace::directory();

var_dump($first->name, $second->name);
var_dump($first->path !== $second->path);
var_dump(dirname($first->path) === TestContext::instance()->workspaceDirectory);
var_dump(dirname($second->path) === dirname($first->path));
var_dump(file_exists($first), file_exists($second));

?>
--EXPECT--
string(11) "directory-1"
string(11) "directory-2"
bool(true)
bool(true)
bool(true)
bool(false)
bool(false)
