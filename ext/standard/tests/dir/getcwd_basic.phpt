--TEST--
Test getcwd() function : basic functionality
--FILE--
<?php
/*
 * Test basic functionality of getcwd()
 */

echo "*** Testing getcwd() : basic functionality ***\n";

//create temporary directory for test
$directory = Workspace::directory('getcwd_basic')->create();

var_dump(getcwd());
chdir($directory);
var_dump(getcwd());
?>
--EXPECTF--
*** Testing getcwd() : basic functionality ***
string(%d) "%s"
string(%d) "%s%egetcwd_basic"
