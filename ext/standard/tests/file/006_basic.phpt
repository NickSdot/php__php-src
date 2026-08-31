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
echo "*** Testing fileperms(), chmod() with files and dirs ***\n";
$file = Workspace::file()->create();
var_dump( chmod($file, 0755 ) );
printf("%o", fileperms($file) );
echo "\n";
clearstatcache();

$directory = Workspace::directory()->create();
var_dump( chmod( $directory, 0777 ) );
printf("%o", fileperms($directory) );
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
