--TEST--
Bug #53885 (ZipArchive segfault with FL_UNCHANGED on empty archive)
--EXTENSIONS--
zip
--FILE--
<?php
$fname = TestPath::touch();
$nx=new ZipArchive();
$nx->open($fname);
$nx->locateName("a",ZIPARCHIVE::FL_UNCHANGED);
$nx->statName("a",ZIPARCHIVE::FL_UNCHANGED);
?>
==DONE==
--EXPECTF--
Deprecated: ZipArchive::open(): Using empty file as ZipArchive is deprecated in %s
==DONE==
