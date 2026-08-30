--TEST--
SPL: Spl File Info test getExtension with leading dot
--FILE--
<?php
$file = TestPath::file('.test')->create()->path;
$fileInfo = new SplFileInfo($file);

var_dump($fileInfo->getExtension());
?>
--EXPECT--
string(4) "test"
