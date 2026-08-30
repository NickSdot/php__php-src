--TEST--
SPL: Spl File Info test getInode
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
if (substr(PHP_OS, 0, 3) == 'WIN') die("skip this test not for Windows platforms");
?>
--FILE--
<?php

//file
$file = TestPath::file()->create();
$fileInfo = new SplFileInfo($file->path);
$result = shell_exec('ls -i ' . escapeshellarg($file->path));
var_dump($fileInfo->getInode() == (int) $result);

?>
--EXPECT--
bool(true)
