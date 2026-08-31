--TEST--
Bug #76584 (PharFileInfo::decompress not working)
--EXTENSIONS--
phar
zlib
--INI--
phar.readonly=0
--FILE--
<?php
$archive = Workspace::file('archive.phar');
$phar = new Phar($archive->path);
$phar->addFromString('76584.txt', 'This is a test file.');
$file = $phar['76584.txt'];
var_dump($file->compress(Phar::GZ));
var_dump($file->isCompressed());
var_dump($file->decompress());
var_dump($file->isCompressed());
$output = Workspace::directory()->create();
$outputFile = $output->file('76584.txt');
var_dump($phar->extractTo($output->path));
echo file_get_contents($outputFile);
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(false)
bool(true)
This is a test file.
