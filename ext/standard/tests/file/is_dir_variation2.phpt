--TEST--
Test is_dir() function: usage variations - links
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) == 'WIN') {
    die('skip Do not run on Windows');
}
?>
--FILE--
<?php
/* Testing is_dir() with dir, soft & hard link to dir,
     and with file, soft & hard link to file */

$dir_symlink = Workspace::file();
$dir_hardlink = Workspace::file();
$filename = Workspace::file();
$file_symlink = Workspace::file();
$file_hardlink = Workspace::file();

echo "*** Testing is_dir() with dir and links to dir ***\n";
echo "-- With dir --\n";
$dirname = Workspace::directory()->create();
var_dump( is_dir($dirname) );
clearstatcache();

echo "-- With symlink --\n";
symlink($dirname, $dir_symlink);
var_dump( is_dir($dir_symlink) );  //is_dir() resolves symlinks
clearstatcache();

echo "-- With hardlink --";
link($dirname, $dir_hardlink); //Not permitted to create hard-link to a dir
var_dump( is_dir($dir_hardlink) );
clearstatcache();

echo "\n*** Testing is_dir() with file and links to a file ***\n";
echo "-- With file --\n";
fclose( fopen($filename, "w") );
var_dump( is_dir($filename) );
clearstatcache();

echo "-- With symlink --\n";
symlink($filename, $file_symlink);
var_dump( is_dir($file_symlink) );
clearstatcache();

echo "-- With hardlink --\n";
link($filename, $file_hardlink);
var_dump( is_dir($file_hardlink) );
clearstatcache();

echo "\n*** Done ***";
?>
--EXPECTF--
*** Testing is_dir() with dir and links to dir ***
-- With dir --
bool(true)
-- With symlink --
bool(true)
-- With hardlink --
Warning: link(): %s in %s on line %d
bool(false)

*** Testing is_dir() with file and links to a file ***
-- With file --
bool(false)
-- With symlink --
bool(false)
-- With hardlink --
bool(false)

*** Done ***
