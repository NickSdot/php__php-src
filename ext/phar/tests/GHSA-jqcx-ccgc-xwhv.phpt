--TEST--
GHSA-jqcx-ccgc-xwhv (Buffer overflow and overread in phar_dir_read())
--EXTENSIONS--
phar
--INI--
phar.readonly=0
--FILE--
<?php
$archive = Workspace::file('archive.phar');
$phar = new Phar($archive->path);
$phar->startBuffering();
$phar->addFromString(str_repeat('A', PHP_MAXPATHLEN - 1), 'This is the content of file 1.');
$phar->addFromString(str_repeat('B', PHP_MAXPATHLEN - 1).'C', 'This is the content of file 2.');
$phar->stopBuffering();

$handle = opendir('phar://' . $archive->path);
var_dump(strlen(readdir($handle)));
// Must not be a string of length PHP_MAXPATHLEN+1
var_dump(readdir($handle));
closedir($handle);
?>
--EXPECTF--
int(%d)
bool(false)
