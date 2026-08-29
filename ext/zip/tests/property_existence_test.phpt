--TEST--
Property existence test can cause a crash
--EXTENSIONS--
zip
--FILE--
<?php

$archive = new ZipArchive(TestPath::file('property_existence.zip')->path);
var_dump(array_column([$archive], 'lastId'));

?>
--EXPECT--
array(1) {
  [0]=>
  int(-1)
}
