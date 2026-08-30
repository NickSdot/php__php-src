--TEST--
Bug #79912 (Phar::decompressFiles not working)
--EXTENSIONS--
phar
zlib
--INI--
phar.readonly=0
--FILE--
<?php
$archive = TestPath::file('bug79912.phar');
$phar = new Phar($archive->path);
$output = TestPath::directory();
$outputFile = $output->file('test.txt');
mkdir($output->path);
$phar->addFromString("test.txt", "This is a test file.This is a test file.This is a test file.");
$file = $phar["test.txt"];
var_dump($file->compress(Phar::GZ)); //true (success)
var_dump($file->getContent());
var_dump($file->isCompressed()); //true (the file is compressed)
var_dump($phar->decompressFiles()); //true (success)
var_dump($file->isCompressed()); //false (the file should not be compressed anymore)
var_dump($phar->extractTo($output->path)); //true
var_dump(file_get_contents($outputFile)); //the extracted file in the folder should be decompressed
?>
--EXPECT--
bool(true)
string(60) "This is a test file.This is a test file.This is a test file."
bool(true)
bool(true)
bool(false)
bool(true)
string(60) "This is a test file.This is a test file.This is a test file."
