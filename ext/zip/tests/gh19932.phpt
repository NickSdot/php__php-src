--TEST--
GH-19932 (ZipArchive::setEncryptionName()/setEncryptionIndex() memory leak)
--EXTENSIONS--
zip
--SKIPIF--
<?php if (!method_exists('ZipArchive', 'setEncryptionName')) die('skip encryption not supported'); ?>
--FILE--
<?php
$file = TestPath::file('archive.zip');
$zip = new ZipArchive();
$zip->open($file, ZipArchive::CREATE);
$zip->addFromString("test.txt", "test");
$zip->setEncryptionName("test.txt", ZipArchive::EM_AES_256, "password");
$zip->setEncryptionName("test.txt", ZipArchive::EM_AES_256, "password");
$zip->setEncryptionIndex("0", ZipArchive::EM_AES_256, "password");
$zip->setEncryptionIndex("0", ZipArchive::EM_AES_256, "password");
$zip->close();
echo "OK";
?>
--EXPECT--
OK
