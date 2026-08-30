--TEST--
addGlob with empty options
--EXTENSIONS--
zip
--FILE--
<?php

$file = TestPath::touch('archive.zip');

$zip = new ZipArchive();
$zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
$zip->addGlob(__FILE__, 0, []);
var_dump($zip->statIndex(0)['name'] === __FILE__);
$zip->close();

?>
--EXPECT--
bool(true)
