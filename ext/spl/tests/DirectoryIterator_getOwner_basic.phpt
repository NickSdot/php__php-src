--TEST--
SPL: DirectoryIterator test getOwner
--EXTENSIONS--
posix
--SKIPIF--
<?php
if (PHP_OS_FAMILY === 'Windows') { die('SKIP Testing file ownership, not available for Windows'); }
if (posix_geteuid() == 0) die('SKIP Cannot run test as root.');
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
$directory = TestPath::directory()->create();
$dir = new DirectoryIterator($directory);
$expected = fileowner($directory);
$actual = $dir->getOwner();
var_dump($expected == $actual);
?>
--EXPECT--
bool(true)
