--TEST--
Bug #77322 (PharData::addEmptyDir('/') Possible integer overflow)
--EXTENSIONS--
phar
--FILE--
<?php
$zipArchive = TestPath::file('archive.zip');
$zip = new PharData($zipArchive->path);
$zip->addEmptyDir('/');
var_dump($zip->count());

$tarArchive = TestPath::file('archive.tar');
$tar = new PharData($tarArchive->path);
$tar->addEmptyDir('/');
var_dump($tar->count());
?>
--EXPECT--
int(1)
int(1)
