--TEST--
SPL: DirectoryIterator test getGroup
--EXTENSIONS--
posix
--SKIPIF--
<?php
if (PHP_OS_FAMILY === 'Windows') { die('SKIP Testing file groups, not available for Windows'); }
if (posix_geteuid() == 0) { die('SKIP Cannot run test as root.'); }
?>
--CREDITS--
Cesare D'Amico <cesare.damico@gruppovolta.it>
Andrea Giorgini <agiorg@gmail.com>
Filippo De Santis <fd@ideato.it>
Daniel Londero <daniel.londero@gmail.com>
Francesco Trucchia <ft@ideato.it>
Jacopo Romei <jacopo@sviluppoagile.it>
#Test Fest Cesena (Italy) on 2009-06-20
--FILE--
<?php
$dirname = TestPath::directory('DirectoryIterator_getGroup_basic')->path;
mkdir($dirname);
$dir = new DirectoryIterator($dirname);
$expected = filegroup($dirname);
$actual = $dir->getGroup();
var_dump($expected == $actual);
?>
--EXPECT--
bool(true)
