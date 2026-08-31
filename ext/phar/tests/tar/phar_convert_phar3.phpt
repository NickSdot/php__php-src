--TEST--
Phar::convertToPhar() bzipped
--EXTENSIONS--
phar
bz2
--INI--
phar.require_hash=0
phar.readonly=0
--FILE--
<?php

$archive = Workspace::file('archive.phar');
$archive2 = Workspace::file('archive-copy.phar');
$fname = $archive->path;
$fname2 = $archive2->path;

$phar = new Phar($fname);
$phar['a.txt'] = 'some text';
$phar->stopBuffering();
var_dump($phar->isFileFormat(Phar::TAR));
var_dump(strlen($phar->getStub()));

$phar = $phar->convertToExecutable(Phar::TAR);
var_dump($phar->isFileFormat(Phar::TAR));
var_dump($phar->getStub());

$phar['a'] = 'hi there';

$phar = $phar->convertToExecutable(Phar::PHAR, Phar::BZ2);
var_dump($phar->isFileFormat(Phar::PHAR));
var_dump($phar->isCompressed());
var_dump(strlen($phar->getStub()));

copy($fname . '.bz2', $fname2);

$phar = new Phar($fname2);
var_dump($phar->isFileFormat(Phar::PHAR));
var_dump($phar->isCompressed() == Phar::BZ2);
var_dump(strlen($phar->getStub()));

?>
--EXPECT--
bool(false)
int(6659)
bool(true)
string(60) "<?php // tar-based phar archive stub file
__HALT_COMPILER();"
bool(true)
int(8192)
int(6659)
bool(true)
bool(true)
int(6659)
