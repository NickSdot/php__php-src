--TEST--
Test rename() function: usage variations-4
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) == 'WIN') {
    die('skip.. only for Linux');
}
?>
--FILE--
<?php

$file_path = TestPath::directory();

$dest_dir = $file_path->directory();
$filename = $file_path->file();
$linkname = $file_path->file();
$dest_linkname = $file_path->file();
$dest_link = $dest_dir->file();
// create the $dest_dir
mkdir($file_path);
mkdir($dest_dir);

echo "\n*** Testing rename() on hard links ***\n";
var_dump(touch($filename));

var_dump(link($filename, $linkname));

//rename the link to a new name in the same dir
var_dump( rename( $filename, $dest_linkname) );
//ensure that link was renamed
var_dump( file_exists($filename) );  // expecting false
var_dump( file_exists($dest_linkname) );  // expecting true

// rename a hard link across dir
var_dump( rename($dest_linkname, $dest_link) );
//ensure that link got renamed
var_dump( file_exists($dest_linkname) );  // expecting false
var_dump( file_exists($dest_link) ); // expecting true

// delete the link file now
unlink($dest_link);

echo "Done\n";
?>
--EXPECT--
*** Testing rename() on hard links ***
bool(true)
bool(true)
bool(true)
bool(false)
bool(true)
bool(true)
bool(false)
bool(true)
Done
