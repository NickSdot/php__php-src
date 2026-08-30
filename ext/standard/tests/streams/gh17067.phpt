--TEST--
GH-17067 (glob:// wrapper doesn't cater to CWD for ZTS builds)
--FILE--
<?php
$dir = TestPath::directory();
mkdir($dir);
touch("$dir/foo");

chdir($dir);
var_dump(scandir("glob://*"));
?>
--EXPECT--
array(1) {
  [0]=>
  string(3) "foo"
}
