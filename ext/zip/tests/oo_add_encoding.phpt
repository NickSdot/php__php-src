--TEST--
ziparchive::addEmptyDir with encoding option
--EXTENSIONS--
zip
--SKIPIF--
<?php
/* $Id$ */
?>
--FILE--
<?php

include __DIR__ . '/utils.inc';
$file = TestPath::file();
$zip = new ZipArchive;
if (!$zip->open($file, ZipArchive::CREATE)) {
    exit('failed');
}

$zip->addEmptyDir('foo');
$zip->addEmptyDir(chr(0x82), ZipArchive::FL_ENC_CP437);
$zip->addEmptyDir('è', ZipArchive::FL_ENC_UTF_8);

$zip->addFromString('bar', __FILE__);
$zip->addFromString(chr(0x91), __FILE__, ZipArchive::FL_ENC_CP437);
$zip->addFromString('€', __FILE__, ZipArchive::FL_ENC_UTF_8);

if ($zip->status == ZIPARCHIVE::ER_OK) {
    dump_entries_name($zip);
    $zip->close();
} else {
    echo "failed\n";
}
?>
--EXPECT--
0 foo/
1 é/
2 è/
3 bar
4 æ
5 €
