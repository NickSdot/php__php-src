--TEST--
Test open_basedir configuration for glob
--INI--
open_basedir=.
--FILE--
<?php
Workspace::useRelative();
$dir = Workspace::directory('globtest1')->create();
Workspace::directory('globtest2')->create();
chdir($dir);
var_dump(glob("../globtest*"));
?>
--EXPECT--
array(1) {
  [0]=>
  string(12) "../globtest1"
}
