--TEST--
Bug #76857 (Can read "non-existant" files)
--FILE--
<?php
$file = TestPath::file();
file_put_contents($file, 'test data');
$path = "foobar://google.com/../../" . $file->name;
chdir($file->directory);
var_dump(file_exists($path));
var_dump(file_get_contents($path, false, null, 0, 10));
?>
--EXPECTF--
Warning: file_exists(): Unable to find the wrapper "foobar" - did you forget to enable it when you configured PHP? in %s on line %d
bool(true)

Warning: file_get_contents(): Unable to find the wrapper "foobar" - did you forget to enable it when you configured PHP? in %s on line %d
string(9) "test data"
