--TEST--
Test rename() function: usage variations
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) != 'WIN') {
    die('skip.. only for Windows');
}
?>
--FILE--
<?php
require __DIR__.'/file.inc';

$dest_dir = Workspace::directory()->create();
$source = Workspace::file();
$numeric = Workspace::file('12346');

/* Renaming a file and directory to numeric name */
echo "\n*** Testing rename() by renaming a file and directory to numeric name ***\n";
$fp = fopen($source, "w");
fclose($fp);

// renaming existing file to numeric name
var_dump( rename($source, $numeric) );

// ensure that rename worked fine
var_dump( file_exists($source) );  // expecting false
var_dump( file_exists($numeric) );  // expecting true

unlink($numeric);

// renaming a directory to numeric name
var_dump( rename($dest_dir . '/', $numeric) );

// ensure that rename worked fine
var_dump( file_exists($dest_dir) );  // expecting false
var_dump( file_exists($numeric) );  // expecting true

echo "Done\n";
?>
--EXPECT--
*** Testing rename() by renaming a file and directory to numeric name ***
bool(true)
bool(false)
bool(true)
bool(true)
bool(false)
bool(true)
Done
