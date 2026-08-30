--TEST--
Bug #79283 (Segfault in libmagic patch contains a buffer overflow)
--EXTENSIONS--
fileinfo
--FILE--
<?php
$magic_file = TestPath::file();
file_put_contents($magic_file, "
0	regex	\\0\\0\\0\\0	Test
");

$finfo = new finfo(FILEINFO_NONE, $magic_file);
var_dump($finfo->buffer("buffer\n"));
?>
--EXPECT--
string(10) "ASCII text"
