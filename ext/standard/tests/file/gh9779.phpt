--TEST--
Bug GH-9779 (stream_copy_to_stream doesn't work anymore with resource opened in a append mode)
--FILE--
<?php

$src = TestPath::file()->path;
$dest = TestPath::file()->path;

file_put_contents($src, "bar");
file_put_contents($dest, "foo");
$sourceHandle = fopen($src, "r");
$destHandle = fopen($dest, "a");
stream_copy_to_stream($sourceHandle, $destHandle);
fclose($sourceHandle);
fclose($destHandle);
var_dump(file_get_contents($dest));
?>
--EXPECTF--
string(6) "foobar"
