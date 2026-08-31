--TEST--
ZipArchive::registerCancelCallback() with a normal callback
--EXTENSIONS--
zip
--SKIPIF--
<?php
/* $Id$ */
if (!method_exists('ZipArchive', 'registerCancelCallback')) die('skip libzip too old');
?>
--INI--
date.timezone=UTC
--FILE--
<?php
$file = Workspace::file();

$zip = new ZipArchive;
if (!$zip->open($file, ZIPARCHIVE::CREATE)) {
    exit('failed');
}

var_dump($zip->registerCancelCallback(function () {
    // Always cancel
    return -1;
}));
var_dump($zip->addFromString(PHP_BINARY, 'entry #1'));

var_dump($zip->close());
var_dump($zip->status == ZipArchive::ER_CANCELLED);
var_dump($zip->getStatusString());
?>
Done
--EXPECTF--
bool(true)
bool(true)

Warning: ZipArchive::close(): Operation cancelled in %s
bool(false)
bool(true)
string(19) "Operation cancelled"
Done
