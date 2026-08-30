--TEST--
Phar: tar-based phar, make new gzipped tar
--EXTENSIONS--
phar
zlib
--INI--
phar.readonly=0
--FILE--
<?php

$archive = TestPath::file('archive.phar.tar');
$compressed = TestPath::file('archive.phar.tar.gz');
$copy = TestPath::file('archive-copy.phar.tar.gz');
$fname = $archive->path;
$fname2 = $compressed->path;
$fname3 = $copy->path;

$phar = new Phar($fname);
$phar['test'] = 'hi';
var_dump($phar->isFileFormat(Phar::TAR));
$phar = $phar->compress(Phar::GZ);


copy($fname2, $fname3);

$phar2 = new Phar($fname3);
var_dump($phar2->isFileFormat(Phar::TAR));
var_dump($phar2->isCompressed() == Phar::GZ);

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
