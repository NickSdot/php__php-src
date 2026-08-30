--TEST--
Bug #73877 readlink() returns garbage for UTF-8 paths
File type functions
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) != 'WIN') {
    die('skip only for Windows');
}
?>
--FILE--
<?php

$base = TestPath::directory();
$dir0 = $base->directory('bug73877');
$dir1 = $base->directory('Серёжка');
$junk0 = $base->directory();

mkdir($base);
mkdir($dir0);
mkdir($dir1);
shell_exec("mklink /J $junk0 $dir0");

var_dump(
    readlink($dir0),
    readlink($dir1),
    readlink($junk0),
    strlen(readlink($dir0)) === strlen(readlink($junk0))
);

?>
--EXPECTF--
string(%d) "%sbug73877"
string(%d) "%sСерёжка"
string(%d) "%sbug73877"
bool(true)
