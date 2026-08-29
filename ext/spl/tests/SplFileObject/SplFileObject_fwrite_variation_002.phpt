--TEST--
SplFileObject::fwrite function - writing with two parameters, length > input string length
--FILE--
<?php
$file = TestPath::file('SplFileObject_fwrite_variation_002.txt')->path;
$obj = New SplFileObject($file,'w');
$obj->fwrite('test_write',12);
var_dump(file_get_contents($file));
?>
--EXPECT--
string(10) "test_write"
