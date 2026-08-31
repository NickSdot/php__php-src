--TEST--
GH-17067 (glob:// wrapper doesn't cater to CWD for ZTS builds)
--FILE--
<?php
$dir = Workspace::directory()->create();
$dir->file('foo')->create();

chdir($dir);
var_dump(scandir("glob://*"));
?>
--EXPECT--
array(1) {
  [0]=>
  string(3) "foo"
}
