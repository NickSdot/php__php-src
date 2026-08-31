--TEST--
GH-18431 (Registering ZIP progress callback twice doesn't work)
--EXTENSIONS--
zip
--FILE--
<?php
$file = Workspace::file('archive.zip');
$callback = var_dump(...);
$zip = new ZipArchive;
$zip->open($file, ZIPARCHIVE::CREATE);
$zip->registerProgressCallback(0.5, $callback);
$zip->registerProgressCallback(0.5, $callback);
$zip->addFromString('foo', 'entry #1');
?>
--EXPECT--
float(0)
float(1)
