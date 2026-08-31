--TEST--
Property existence test can cause a crash
--EXTENSIONS--
zip
--FILE--
<?php

$archive = new ZipArchive(Workspace::file());
var_dump(array_column([$archive], 'lastId'));

?>
--EXPECT--
array(1) {
  [0]=>
  int(-1)
}
