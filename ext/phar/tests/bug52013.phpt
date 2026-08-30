--TEST--
Test for bug 52013 about Phar::decompressFiles().
--DESCRIPTION--
Test for a bug where Phar::decompressFiles() mistakenly throws BadMethodCallException.
http://bugs.php.net/bug.php?id=52013
--CREDITS--
Frederic Hardy frederic.hardy@mageekbox.net
--EXTENSIONS--
phar
zlib
--INI--
phar.require_hash=0
phar.readonly=0
open_basedir=
--FILE--
<?php
$directory = TestPath::directory();
mkdir($directory->path);
$file = $directory->file('1.php');
file_put_contents($file, str_repeat(' ', 1455));

$archive = TestPath::file('compressed.phar');
$phar = new Phar($archive->path);
$phar->buildFromDirectory($directory->path, '/\.php$/');
$phar->setSignatureAlgorithm(Phar::SHA1);
$phar->compressFiles(Phar::GZ);
$phar->decompressFiles();

echo 'ok';
?>
--EXPECT--
ok
