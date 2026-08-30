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
$zip = new ZipArchive();
$zip->open(TestPath::file(), ZIPARCHIVE::CREATE);
$zip->addPattern("/noexists/");
?>
okey
--EXPECT--
okey
