--TEST--
Bug #81490 (ZipArchive::extractTo() may leak memory)
--EXTENSIONS--
zip
--FILE--
<?php
$zip = new ZipArchive();
$archive = TestPath::file();
$destination = TestPath::directory();
$zip->open($archive, ZipArchive::CREATE|ZipArchive::OVERWRITE);
$zip->addFromString("", "yada yada");
mkdir($destination->path);
$zip->open($archive);
$zip->extractTo($destination, "");
?>
--EXPECT--
