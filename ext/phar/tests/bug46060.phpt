--TEST--
Phar: Bug #46060: addEmptyDir() breaks
--EXTENSIONS--
phar
--SKIPIF--
<?php if (getenv('SKIP_SLOW_TESTS')) die('skip'); ?>
--INI--
phar.require_hash=0
--FILE--
<?php
$archive = TestPath::file('archive.tar');
$archive2 = TestPath::file('archive-copy.tar');
$fname = $archive->path;
$fname2 = $archive2->path;

$phar = new PharData($fname);
$phar->addEmptyDir('blah/');
$phar->addFromString('test/', '');

copy($fname, $fname2);
$phar = new PharData($fname2);

var_dump($phar['blah']->isDir(), $phar['test']->isDir());
?>
--EXPECT--
bool(true)
bool(false)
