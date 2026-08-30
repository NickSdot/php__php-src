--TEST--
GH-10766 (PharData archive created with Phar::Zip format does not keep files metadata (datetime))
--EXTENSIONS--
phar
zip
--INI--
phar.readonly=0
--FILE--
<?php
$archive = TestPath::file('archive.zip');
$phar = new PharData($archive, 0, null, Phar::ZIP);
$phar->addFromString('name', 'contents');
unset($phar);

// Re-read from disk, but using the zip extension because the phar bug will not make it possible
// to use their timestamp methods.
$zip = new ZipArchive();
$zip->open($archive);
var_dump($zip->statName('name')['mtime'] > 315529200 /* earliest possible zip timestamp */);
$zip->close();
?>
--EXPECT--
bool(true)
