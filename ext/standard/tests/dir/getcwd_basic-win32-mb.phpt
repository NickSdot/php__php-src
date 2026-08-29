--TEST--
Test getcwd() function : basic functionality
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) != 'WIN') {
  die("skip Valid only on Windows");
}
?>
--FILE--
<?php
/*
 * Test basic functionality of getcwd()
 */

echo "*** Testing getcwd() : basic functionality ***\n";

//create temporary directory for test, removed in CLEAN section
$directory = TestPath::directory('getcwd_basic-mb')->path;
mkdir($directory);

var_dump(getcwd());
chdir($directory);
var_dump(getcwd());
?>
--EXPECTF--
*** Testing getcwd() : basic functionality ***
string(%d) "%s"
string(%d) "%s%e私はガラスを食べられますgetcwd_basic"
