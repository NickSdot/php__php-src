--TEST--
Test Workspace::file() - anonymous paths - distinct names without creating files
--FILE--
<?php

use TestSuite\Context\TestContext;

$first = Workspace::file();
$second = Workspace::file();

var_dump($first->name, $second->name);
var_dump($first->path !== $second->path);
var_dump($first->directory === TestContext::instance()->workspaceDirectory);
var_dump($second->directory === $first->directory);
var_dump(file_exists($first), file_exists($second));

?>
--EXPECT--
string(6) "file-1"
string(6) "file-2"
bool(true)
bool(true)
bool(true)
bool(false)
bool(false)
