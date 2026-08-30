--TEST--
Bug #41518 (file_exists() warns of open_basedir restriction on non-existent file)
--INI--
open_basedir=.
--FILE--
<?php

$tmp_file = TestPath::touch();
var_dump(file_exists($tmp_file)); //exists
var_dump(file_exists($tmp_file."nosuchfile")); //doesn't exist

echo "Done\n";
?>
--EXPECT--
bool(true)
bool(false)
Done
