--TEST--
Bug #72666 (stat cache clearing inconsistent - exec)
--FILE--
<?php
$filename = TestPath::file()->path;

touch($filename, 1);
var_dump(filemtime($filename));
exec("touch $filename");
var_dump(filemtime($filename) > 1);


touch($filename, 1);
var_dump(filemtime($filename));
shell_exec("touch $filename");
var_dump(filemtime($filename) > 1);
?>
--EXPECT--
int(1)
bool(true)
int(1)
bool(true)
