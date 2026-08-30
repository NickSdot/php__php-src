--TEST--
Phar: PharFileInfo::__destruct
--EXTENSIONS--
phar
--INI--
phar.readonly=0
--FILE--
<?php
$fname = TestPath::file('archive.phar')->path;
$pname = 'phar://' . $fname;

$a = new Phar($fname);
$a['a/subdir/here'] = 'hi';

$b = new PharFileInfo($pname . '/a/subdir');
unset($b);

$b = new PharFileInfo($pname . '/a/subdir/here');
unset($b);
?>
===DONE===
--EXPECT--
===DONE===
