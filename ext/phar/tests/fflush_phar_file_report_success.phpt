--TEST--
fflush() on phar file should report success
--EXTENSIONS--
phar
--INI--
phar.readonly=0
--FILE--
<?php

$archive = Workspace::file('archive.phar');
$phar = new Phar($archive->path);
$phar->addFromString('test', 'contents');
unset($phar);

$f = fopen('phar://' . $archive->path . '/test', 'w');
var_dump(fflush($f));
var_dump(fclose($f));

?>
--EXPECT--
bool(true)
bool(true)
