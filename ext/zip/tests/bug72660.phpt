--TEST--
Bug #72660 (NULL Pointer dereference in zend_virtual_cwd)
--EXTENSIONS--
zip
--SKIPIF--
<?php
if(!PHP_ZTS) { die('skip ZTS required'); }
?>
--FILE--
<?php
$file = Workspace::file('archive.zip');
$zip = new ZipArchive();
$zip->open($file, ZIPARCHIVE::CREATE);
$zip->addPattern("/noexists/");
?>
okey
--EXPECT--
okey
