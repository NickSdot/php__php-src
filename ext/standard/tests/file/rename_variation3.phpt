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

// create the $dest_dir
$dest_dir = Workspace::directory()->create();
$filename = Workspace::file();
$linkname = Workspace::file();
$dest_linkname = Workspace::file();
$dest_link = $dest_dir->file();

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
