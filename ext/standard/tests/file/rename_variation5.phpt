--TEST--
Test rename() function: usage variations-6
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) == 'WIN') {
    die('skip.. only for Linux');
}
?>
--FILE--
<?php

/* test rename() by trying to rename an existing file/dir/link to the same name
  and one another */
// create a dir
$file_path = TestPath::directory();
$dirname = $file_path->directory();
$filename = $file_path->file();
$linkname = $file_path->file();
mkdir($file_path);
mkdir($dirname);
//create a file
$fp = fopen($filename, "w");
fclose($fp);
// create a link
symlink($filename, $linkname);

echo "\n-- Renaming link to same link name --\n";
var_dump( rename($linkname, $linkname) );

echo "\n-- Renaming file to same file name --\n";
var_dump( rename($filename, $filename) );

echo "\n-- Renaming directory to same directory name --\n";
var_dump( rename($dirname, $dirname) );

echo "\n-- Renaming existing link to existing directory name --\n";
var_dump( rename($linkname, $dirname) );
echo "\n-- Renaming existing link to existing file name --\n";
var_dump( rename($linkname, $filename) );

echo "\n-- Renaming existing file to existing directory name --\n";
var_dump( rename($filename, $dirname) );
echo "\n-- Renaming existing file to existing link name --\n";
var_dump( rename($filename, $linkname) );

echo "\n-- Renaming existing directory to existing file name --\n";
$fp = fopen($filename, "w");
fclose($fp);
var_dump( rename($dirname, $filename) );
echo "\n-- Renaming existing directory to existing link name --\n";
var_dump( rename($dirname, $linkname) );

echo "Done\n";
?>
--EXPECTF--
-- Renaming link to same link name --
bool(true)

-- Renaming file to same file name --
bool(true)

-- Renaming directory to same directory name --
bool(true)

-- Renaming existing link to existing directory name --

Warning: rename(): Is a directory in %s on line %d
bool(false)

-- Renaming existing link to existing file name --
bool(true)

-- Renaming existing file to existing directory name --

Warning: rename(): Is a directory in %s on line %d
bool(false)

-- Renaming existing file to existing link name --
bool(true)

-- Renaming existing directory to existing file name --

Warning: rename(): Not a directory in %s on line %d
bool(false)

-- Renaming existing directory to existing link name --

Warning: rename(): Not a directory in %s on line %d
bool(false)
Done
