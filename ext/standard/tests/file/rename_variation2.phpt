--TEST--
Test rename() function: usage variations-3
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) == 'WIN') {
    die('skip.. only for Linux');
}
?>
--FILE--
<?php

/* Testing rename() on soft and hard links with different permissions */
echo "\n*** Testing rename() on soft links ***\n";
// create the file
$filename = Workspace::file();
var_dump(touch($filename));

// create the soft links to the file
$linkname = Workspace::file();
var_dump(symlink($filename, $linkname));

//rename the link to a new name in the same dir
$dest_linkname = Workspace::file();
var_dump( rename( $linkname, $dest_linkname) );
//ensure that link was renamed
clearstatcache();
var_dump( file_exists($linkname) );  // expecting false
var_dump( file_exists($dest_linkname) );  // expecting true

// rename a link across dir
$dest_link = Workspace::directory()->create()->file($dest_linkname->name);
var_dump( rename($dest_linkname, $dest_link));
//ensure that link got renamed
clearstatcache();
var_dump( file_exists($dest_linkname) );  // expecting false
var_dump( file_exists($dest_link) ); // expecting true

echo "Done\n";
?>
--EXPECT--
*** Testing rename() on soft links ***
bool(true)
bool(true)
bool(true)
bool(false)
bool(true)
bool(true)
bool(false)
bool(true)
Done
