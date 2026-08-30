--TEST--
Test open_basedir configuration for glob
--INI--
open_basedir=.
--FILE--
<?php
TestContext::useRelativeWorkspace();
$dir = TestPath::directory('globtest1');
$dir2 = TestPath::directory('globtest2');
mkdir($dir);
mkdir($dir2);
chdir($dir);
var_dump(glob("../globtest*"));
?>
--EXPECT--
array(1) {
  [0]=>
  string(12) "../globtest1"
}
