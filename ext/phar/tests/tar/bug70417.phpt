--TEST--
Bug #70417 (PharData::compress() doesn't close temp file)
--EXTENSIONS--
phar
zlib
--FILE--
<?php
$archive = TestPath::file('archive.tar');
$compressed = TestPath::file('archive.tar.gz');
$filename = $archive->path;
$resBefore = count(get_resources());
$arch = new PharData($filename);
$arch->addFromString('foo', 'bar');
$arch->addFromString('foo2', 'baz');
$arch->compress(Phar::GZ);
unset($arch);
$resAfter = count(get_resources());
var_dump($resAfter - $resBefore);
?>
--EXPECT--
int(0)
