--TEST--
Phar: PHP bug #46178: "memory leak in ext/phar"
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
phar.readonly=0
--FILE--
<?php
$fname = Workspace::file('archive.phar')->path;

$phar = new Phar($fname);
$phar['long/path/name.txt'] = 'hi';
$phar->addEmptyDir('long/path');
?>
===DONE===
--EXPECT--
===DONE===
