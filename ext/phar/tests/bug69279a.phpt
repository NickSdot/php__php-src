--TEST--
Bug #69279 (Compressed ZIP Phar extractTo() creates garbage files)
--EXTENSIONS--
phar
zlib
--FILE--
<?php
$phar = new PharData(__DIR__ . '/bug69279a.zip');
$output = TestPath::directory();
mkdir($output->path);
$file1 = $output->file('1.txt');
$file2 = $output->file('2.txt');
$file3 = $output->file('3.txt');
var_dump($phar->extractTo($output->path, null, true));
var_dump(strncmp(file_get_contents($file1), 'Lorem ipsum', 11));
var_dump(strncmp(file_get_contents($file2), 'foo', 3));
var_dump(strncmp(file_get_contents($file3), 'Lorem ipsum', 11));
?>
--EXPECT--
bool(true)
int(0)
int(0)
int(0)
