--TEST--
Bug #70019 Files extracted from archive may be placed outside of destination directory
--EXTENSIONS--
phar
zlib
--FILE--
<?php
$dir = TestPath::directory();
$extracted = $dir->file('ThisIsATestFile.txt');
$phar = new PharData(__DIR__."/bug70019.zip");
if(!is_dir($dir)) {
  mkdir($dir->path);
}
$phar->extractTo($dir->path);
var_dump(file_exists($extracted));
?>
--EXPECT--
bool(true)
