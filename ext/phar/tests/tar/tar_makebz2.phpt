--TEST--
Phar: tar-based phar, make new bzipped tar
--EXTENSIONS--
phar
bz2
--INI--
phar.readonly=0
--FILE--
<?php

$tarArchive = Workspace::file('archive.tar');
$compressed = Workspace::file('archive.tar.bz2');
$copy = Workspace::file('archive-copy.tar.bz2');
$fname = $tarArchive->path;
$fname2 = $compressed->path;
$fname3 = $copy->path;

$phar = new Phar($fname);
$phar['test'] = 'hi';
var_dump($phar->isFileFormat(Phar::TAR));
$phar = $phar->compress(Phar::BZ2);

copy($fname2, $fname3);

$phar2 = new Phar($fname3);
var_dump($phar2->isFileFormat(Phar::TAR));
var_dump($phar2->isCompressed() == Phar::BZ2);

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
