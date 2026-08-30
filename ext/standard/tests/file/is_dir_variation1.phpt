--TEST--
Test is_dir() function: usage variations - dir/subdir
--FILE--
<?php
/* Testing is_dir() with base and sub dirs */

$file_path = __DIR__;
$directory = TestPath::directory();
$subdirectory = $directory->directory();

echo "-- Testing is_dir() with an empty dir --\n";
$dirname = $directory;
mkdir($dirname);
var_dump( is_dir($dirname) );
clearstatcache();

echo "-- Testing is_dir() with a subdir in base dir --\n";
$subdirname = $subdirectory;
mkdir($subdirname);
var_dump( is_dir($subdirname) );
var_dump( is_dir($dirname) );

echo "\n*** Done ***";
?>
--EXPECT--
-- Testing is_dir() with an empty dir --
bool(true)
-- Testing is_dir() with a subdir in base dir --
bool(true)
bool(true)

*** Done ***
