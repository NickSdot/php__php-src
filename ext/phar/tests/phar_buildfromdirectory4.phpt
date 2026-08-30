--TEST--
Phar::buildFromDirectory(), directory exists
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
phar.readonly=0
open_basedir=
--FILE--
<?php

$directory = TestPath::directory('testdir4');
mkdir($directory->path);
$files = [];
foreach(range(1, 4) as $i) {
    $files[$i] = $directory->file("file$i.txt");
    file_put_contents($files[$i], "some content for file $i");
}

try {
    $archive = TestPath::file('buildfromdirectory4.phar');
    $phar = new Phar($archive->path);
    $a = $phar->buildFromDirectory($directory->path);
    asort($a);
    var_dump($a);
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

var_dump(file_exists($archive));

?>
--EXPECTF--
array(4) {
  ["file1.txt"]=>
  string(%d) "%stestdir4%cfile1.txt"
  ["file2.txt"]=>
  string(%d) "%stestdir4%cfile2.txt"
  ["file3.txt"]=>
  string(%d) "%stestdir4%cfile3.txt"
  ["file4.txt"]=>
  string(%d) "%stestdir4%cfile4.txt"
}
bool(true)
