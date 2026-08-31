--TEST--
SplFileInfo::openFile() in write mode
--EXTENSIONS--
phar
--INI--
phar.readonly=0
--FILE--
<?php

$archive = Workspace::file('archive.phar');
$phar = new Phar($archive->path);
$phar->addFromString('test', 'contents');
var_dump($phar['test']->openFile('w'));

?>
--EXPECTF--
object(SplFileObject)#%d (%d) {
  ["pathName":"SplFileInfo":private]=>
  string(%d) "phar://%stest"
  ["fileName":"SplFileInfo":private]=>
  string(4) "test"
  ["openMode":"SplFileObject":private]=>
  string(1) "w"
  ["delimiter":"SplFileObject":private]=>
  string(1) ","
  ["enclosure":"SplFileObject":private]=>
  string(1) """
}
