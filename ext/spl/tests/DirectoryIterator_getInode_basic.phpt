--TEST--
SPL: Spl Directory Iterator test getInode
--CREDITS--
Cesare D'Amico <cesare.damico@gruppovolta.it>
Andrea Giorgini <agiorg@gmail.com>
Filippo De Santis <fd@ideato.it>
Daniel Londero <daniel.londero@gmail.com>
Francesco Trucchia <ft@ideato.it>
Jacopo Romei <jacopo@sviluppoagile.it>
#Test Fest Cesena (Italy) on 2009-06-20
--SKIPIF--
<?php
if (PHP_OS_FAMILY === 'Windows') { die('SKIP Testing file inodes, not available for Windows'); }
?>
--FILE--
<?php

//file
$directory = Workspace::directory()->create();
$dirIterator = new DirectoryIterator($directory);
var_dump(decoct($dirIterator->getInode()));

?>
--EXPECTF--
string(%d) "%d"
