--TEST--
Bug #72666 (stat cache clearing inconsistent - plain wrapper)
--FILE--
<?php
$filename = Workspace::file()->path;

file_put_contents($filename, "test");
$mtime1 = filemtime($filename);
$fd = fopen($filename, "w");
sleep(1);
var_dump(fwrite($fd, "data"));
$mtime2 = filemtime($filename);
var_dump($mtime2 > $mtime1);
?>
--EXPECT--
int(4)
bool(true)
