--TEST--
Bug #68557 (SplFileInfo::getPathname() may be broken)
--FILE--
<?php
$dir = TestPath::directory()->create();
$dir->file()->create();
$dir->file()->create();

$d = new DirectoryIterator($dir);

$d->seek(0);
$path0 = $d->current()->getPathname();

$d->seek(1);
$path1 = $d->current()->getPathname();

$d->seek(2);
$path2 = $d->current()->getPathname();

$d->seek(0);
var_dump($path0 === $d->current()->getPathname());

$d->seek(1);
var_dump($path1 === $d->current()->getPathname());

$d->seek(2);
var_dump($path2 === $d->current()->getPathname());

$d->seek(0);
var_dump($path0 === $d->current()->getPathname());
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
