--TEST--
Test fileperms() & chmod() functions: basic functionality
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) == 'WIN') {
    die('skip Not on Windows');
}
require __DIR__ . '/../skipif_root.inc';
?>
--FILE--
<?php
$file = TestPath::file();
$directory = TestPath::directory();
$path = __DIR__;

echo "*** Testing fileperms(), chmod() with files and dirs ***\n";
fopen($file->path, "w");
var_dump( chmod($file, 0755 ) );
printf("%o", fileperms($file->path) );
echo "\n";
clearstatcache();

mkdir($directory);
var_dump( chmod( $directory, 0777 ) );
printf("%o", fileperms($directory->path) );
echo "\n";
clearstatcache();

echo "Done\n";
?>
--EXPECT--
*** Testing fileperms(), chmod() with files and dirs ***
bool(true)
100755
bool(true)
40777
Done
