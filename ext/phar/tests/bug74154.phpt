--TEST--
Bug #74154 (Phar extractTo creates empty files)
--EXTENSIONS--
phar
zlib
--FILE--
<?php

$dir = TestPath::directory('bug74154');
mkdir($dir->path);
$file1 = $dir->file('1.txt');
$file2 = $dir->file('2.txt');
file_put_contents($file1, str_repeat('h', 64));
file_put_contents($file2, str_repeat('i', 64));
$source = TestPath::file('bug74154.tar');
$compressed = TestPath::file('bug74154.tar.gz');
$out = TestPath::directory('bug74154_out');
mkdir($out->path);
$out->file('1.txt');
$out->file('2.txt');
$phar = new PharData($source->path);
$phar->buildFromDirectory($dir);

$compPhar = $phar->compress(Phar::GZ);
unset($phar); //make sure that test.tar is closed
unlink($source->path);
unset($compPhar); //make sure that test.tar.gz is closed
$extractingPhar = new PharData($compressed->path);
$extractingPhar->extractTo($out->path);

var_dump(file_get_contents($dir.'_out/1.txt'));
var_dump(file_get_contents($dir.'_out/2.txt'));

?>
--EXPECT--
string(64) "hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh"
string(64) "iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii"
