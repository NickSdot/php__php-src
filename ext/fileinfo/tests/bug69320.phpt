--TEST--
Bug #69320 libmagic crash when running laravel tests
--EXTENSIONS--
fileinfo
--FILE--
<?php

$fname = TestPath::file();
file_put_contents($fname, "foo");
var_dump(finfo_file(finfo_open(FILEINFO_MIME_TYPE), $fname));

?>
--EXPECT--
string(10) "text/plain"
