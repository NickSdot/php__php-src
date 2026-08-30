--TEST--
Bug #45181 (chdir() should clear relative entries in stat cache)
--FILE--
<?php
TestContext::useRelativeWorkspace();
$directory = TestPath::directory();
mkdir($directory);
var_dump(is_dir($directory));
chdir($directory);
var_dump(is_dir($directory->name));
?>
--EXPECT--
bool(true)
bool(false)
