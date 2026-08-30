--TEST--
Bug #72666 (stat cache clearing inconsistent - touch)
--FILE--
<?php
$filename = TestPath::file()->path;

touch($filename);
var_dump(filemtime($filename) > 2);
touch($filename, 1);
var_dump(filemtime($filename));

?>
--EXPECT--
bool(true)
int(1)
