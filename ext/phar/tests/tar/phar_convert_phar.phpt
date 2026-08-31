--TEST--
Phar::convertToPhar() from tar
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
phar.readonly=0
--FILE--
<?php

$archive = Workspace::file('archive.phar');
$archive2 = Workspace::file('archive.2.phar');
$archive3 = Workspace::file('archive.3.phar');
$fname = $archive->path;
$fname2 = $archive2->path;
$fname3 = $archive3->path;

$phar = new Phar($fname);
$phar['a.txt'] = 'some text';
$phar->stopBuffering();
var_dump($phar->isFileFormat(Phar::TAR));
var_dump(strlen($phar->getStub()));

$phar = $phar->convertToExecutable(Phar::TAR);
var_dump($phar->isFileFormat(Phar::TAR));
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
string(60) "<?php // tar-based phar archive stub file
__HALT_COMPILER();"
bool(true)
int(6659)
bool(true)
int(6659)
