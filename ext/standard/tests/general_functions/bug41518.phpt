--TEST--
Bug #41518 (file_exists() warns of open_basedir restriction on non-existent file)
--SKIPIF--
<?php
if (!is_writable(__DIR__)) {
    die("skip");
}
?>
--INI--
open_basedir=.
--FILE--
<?php

$tmp_dir = TestPath::directory();
@mkdir($tmp_dir);
$tmp_file = $tmp_dir->file();

touch($tmp_file);
var_dump(file_exists($tmp_file)); //exists
var_dump(file_exists($tmp_file."nosuchfile")); //doesn't exist

@rmdir($tmp_dir);
echo "Done\n";
?>
--EXPECT--
bool(true)
bool(false)
Done
