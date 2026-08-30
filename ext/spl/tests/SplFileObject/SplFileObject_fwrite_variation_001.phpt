--TEST--
SplFileObject::fwrite function - writing with two parameters length < input string length
--FILE--
<?php
$file = TestPath::file('SplFileObject_fwrite_variation_001.txt')->path;
$obj = New SplFileObject($file,'w');
$obj->fwrite('test_write',4);
var_dump(file_get_contents($file));
?>
--EXPECT--
string(4) "test"
