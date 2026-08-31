--TEST--
Test copy() function: usage variations - links
--SKIPIF--
<?php
if(substr(PHP_OS, 0, 3) == "WIN")
  die("skip Invalid for Windows");
?>
--FILE--
<?php
/* Test copy() function: Trying to create copy of links */

echo "*** Testing copy() with symlink and hardlink ***\n";
$file = Workspace::file()->path;
$file_handle = fopen($file, "w");
fwrite( $file_handle, str_repeat("Hello World, this is 2007 year ....\n", 100) );
fclose($file_handle);

$symlink = Workspace::file()->path;
$hardlink = Workspace::file()->path;

symlink($file, $symlink);  //creating symlink
link($file, $hardlink);  //creating hardlink

echo "Size of source files => \n";
var_dump( filesize($symlink) );  //size of the symlink itself
clearstatcache();
var_dump( filesize($hardlink) );  //size of the file
clearstatcache();

echo "-- Now applying copy() on source link to create copies --\n";
echo "-- With symlink --\n";
 $symlink_copy = Workspace::file()->path;
var_dump( copy($symlink, $symlink_copy) );
var_dump( file_exists($symlink_copy) );
var_dump( is_link($symlink_copy) );
var_dump( is_file($symlink_copy) );
var_dump( filesize($symlink_copy) );
clearstatcache();

echo "-- With hardlink --\n";
 $hardlink_copy = Workspace::file()->path;
var_dump( copy($hardlink, $hardlink_copy) );
var_dump( file_exists($hardlink_copy) );
var_dump( is_link($hardlink_copy) );
var_dump( is_file($hardlink_copy) );
var_dump( filesize($hardlink_copy) );
clearstatcache();

echo "*** Done ***\n";
?>
--EXPECTF--
*** Testing copy() with symlink and hardlink ***
Size of source files => 
int(%d)
int(3600)
-- Now applying copy() on source link to create copies --
-- With symlink --
bool(true)
bool(true)
bool(false)
bool(true)
int(3600)
-- With hardlink --
bool(true)
bool(true)
bool(false)
bool(true)
int(3600)
*** Done ***
