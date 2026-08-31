--TEST--
Phar: bug #71391: NULL Pointer Dereference in phar_tar_setupmetadata()
--EXTENSIONS--
phar
--FILE--
<?php
// duplicate since the tar will change
$archive = Workspace::file('bug71391.test.tar');
copy(__DIR__."/bug71391.tar", $archive);
$p = new PharData($archive->path);
$p->delMetaData();
?>
DONE
--EXPECT--
DONE
