--TEST--
Bug #81490 (ZipArchive::extractTo() may leak memory)
--EXTENSIONS--
zip
--FILE--
<?php
$zip = new ZipArchive();
$archive = Workspace::file();
$destination = Workspace::directory();
$zip->open($archive, ZipArchive::CREATE|ZipArchive::OVERWRITE);
$zip->addFromString("", "yada yada");
$destination->create();
$zip->open($archive);
$zip->extractTo($destination, "");
?>
--EXPECT--
