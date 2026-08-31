--TEST--
Phar::convertToPhar() from zip
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
phar.readonly=0
--FILE--
<?php

$fname = Workspace::file('archive.phar');
$fname2 = Workspace::file('copy.phar');
$fname3 = Workspace::file('archive.3.phar');

$phar = new Phar($fname);
$phar['a.txt'] = 'some text';
$phar->stopBuffering();
var_dump($phar->isFileFormat(Phar::ZIP));
var_dump(strlen($phar->getStub()));

$phar = $phar->convertToExecutable(Phar::ZIP);
var_dump($phar->isFileFormat(Phar::ZIP));
var_dump($phar->getStub());

$phar['a'] = 'hi there';

$phar = $phar->convertToExecutable(Phar::PHAR, Phar::NONE, '.3.phar');
var_dump($phar->isFileFormat(Phar::PHAR));
var_dump(strlen($phar->getStub()));

copy($fname3, $fname2);

$phar = new Phar($fname2);
var_dump($phar->isFileFormat(Phar::PHAR));
var_dump(strlen($phar->getStub()));

?>
--EXPECT--
bool(false)
int(6659)
bool(true)
string(60) "<?php // zip-based phar archive stub file
__HALT_COMPILER();"
bool(true)
int(6659)
bool(true)
int(6659)
