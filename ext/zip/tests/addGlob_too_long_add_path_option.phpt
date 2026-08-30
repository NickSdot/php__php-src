--TEST--
addGlob with too long add_path option
--EXTENSIONS--
zip
--FILE--
<?php

$file = TestPath::file()->create();

$zip = new ZipArchive();
$zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
$zip->addGlob(__FILE__, 0, ['add_path' => str_repeat('A', PHP_MAXPATHLEN - 2)]);
$zip->close();

?>
--EXPECTF--
Warning: ZipArchive::addGlob(): Entry name too long (max: %d, %d given) in %s on line %d
