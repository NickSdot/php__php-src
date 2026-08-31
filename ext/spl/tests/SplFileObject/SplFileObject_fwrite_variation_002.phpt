--TEST--
SplFileObject::fwrite function - writing with two parameters, length > input string length
--FILE--
<?php
$file = Workspace::file();
$obj = New SplFileObject($file,'w');
$obj->fwrite('test_write',12);
var_dump(file_get_contents($file));
?>
--EXPECT--
string(10) "test_write"
