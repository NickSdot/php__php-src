--TEST--
ZipArchive destructor should be called on bailout
--EXTENSIONS--
zip
--FILE--
<?php

function &cb() {}

$file = Workspace::file();
$zip = new ZipArchive;
$zip->open($file, ZIPARCHIVE::CREATE);
$zip->registerCancelCallback(cb(...));
$zip->addFromString('test', 'test');
$fusion = $zip;

?>
--EXPECTF--
Notice: Only variable references should be returned by reference in %s on line %d

Notice: Only variable references should be returned by reference in %s on line %d

Notice: Only variable references should be returned by reference in %s on line %d
