--TEST--
Bug #69279 (Compressed ZIP Phar extractTo() creates garbage files)
--EXTENSIONS--
phar
zlib
--INI--
phar.readonly=0
--FILE--
<?php
$archive = TestPath::file('bug69279.phar.zip');
$extracted = TestPath::file('bug69279.txt');
$w = new Phar($archive->path);
$w["bug69279.txt"] = "Sample content.";
$w->compressFiles(Phar::GZ);
unset($w);

$r = new Phar($archive->path);
var_dump($r["bug69279.txt"]->isCompressed());

$r->extractTo($extracted->directory, NULL, TRUE);
var_dump(file_get_contents($extracted));
?>
--EXPECT--
bool(true)
string(15) "Sample content."
