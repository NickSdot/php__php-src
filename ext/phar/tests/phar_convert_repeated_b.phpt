--TEST--
PharData::convertToZip|Tar|Phar() repeated (phardata_based archives)
--EXTENSIONS--
phar
zlib
--INI--
phar.readonly=1
--FILE--
<?php

$archive = Workspace::file('phar_convert_repeated_b.zip');
$fname = $archive->path;

echo "=================== new PharData() ==================\n";
$phar = new PharData($fname);
$phar['a'] = 'a';
$phar['b'] = 'b';
$phar['c'] = 'c';

var_dump($phar->isFileFormat(Phar::PHAR));
var_dump($phar->isFileFormat(Phar::TAR));
var_dump($phar->isFileFormat(Phar::ZIP));
var_dump($phar->getStub());
var_dump($phar->getAlias());

echo "================= convertToTar() =====================\n";

$phar = $phar->convertToData(Phar::TAR);
var_dump($phar->isFileFormat(Phar::PHAR));
var_dump($phar->isFileFormat(Phar::TAR));
var_dump($phar->isFileFormat(Phar::ZIP));
var_dump($phar->getStub());
var_dump($phar->getAlias());

echo "================= convertToZip() =====================\n";

$phar = $phar->convertToData(Phar::ZIP, Phar::NONE, '.1.zip');
var_dump($phar->isFileFormat(Phar::PHAR));
var_dump($phar->isFileFormat(Phar::TAR));
var_dump($phar->isFileFormat(Phar::ZIP));
var_dump($phar->getStub());
var_dump($phar->getAlias());

echo "================= convertToPhar() ====================\n";

try {
    $phar = $phar->convertToExecutable(Phar::PHAR);
    var_dump($phar->isFileFormat(Phar::PHAR));
    var_dump($phar->isFileFormat(Phar::TAR));
    var_dump($phar->isFileFormat(Phar::ZIP));
    var_dump(strlen($phar->getStub()));
    var_dump($phar->getAlias());
} catch(Exception $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

echo "================ convertToTar(GZ) ====================\n";

$phar = $phar->convertToData(Phar::TAR, Phar::GZ, '.2.tar');
var_dump($phar->isFileFormat(Phar::PHAR));
var_dump($phar->isFileFormat(Phar::TAR));
var_dump($phar->isFileFormat(Phar::ZIP));
var_dump($phar->getStub());
var_dump($phar->getAlias());

echo "================= convertToPhar() ====================\n";

try {
    $phar = $phar->convertToExecutable(Phar::PHAR);
    var_dump($phar->isFileFormat(Phar::PHAR));
    var_dump($phar->isFileFormat(Phar::TAR));
    var_dump($phar->isFileFormat(Phar::ZIP));
    var_dump(strlen($phar->getStub()));
    var_dump($phar->getAlias());
} catch(Exception $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}
?>
--EXPECT--
=================== new PharData() ==================
bool(false)
bool(false)
bool(true)
string(0) ""
NULL
================= convertToTar() =====================
bool(false)
bool(true)
bool(false)
string(0) ""
NULL
================= convertToZip() =====================
bool(false)
bool(false)
bool(true)
string(0) ""
NULL
================= convertToPhar() ====================
UnexpectedValueException: Cannot write out executable phar archive, phar is read-only
================ convertToTar(GZ) ====================
bool(false)
bool(true)
bool(false)
string(0) ""
NULL
================= convertToPhar() ====================
UnexpectedValueException: Cannot write out executable phar archive, phar is read-only
