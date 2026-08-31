--TEST--
Test rename() function: variation
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) != 'WIN') die('skip..  for Windows');
?>
--FILE--
<?php
echo "\n*** Testing rename() on non-existing file ***\n";
$src_name = Workspace::file();
$dest_name = Workspace::file();

// try renaming a non existing file
var_dump( rename($src_name, $dest_name) );

// ensure that $dest_name didn't get created
var_dump( file_exists($src_name) );  // expecting false
var_dump( file_exists($dest_name) ); // expecting false

// rename a existing dir to new name
echo "\n*** Testing rename() on existing directory ***\n";
$dir_name = Workspace::directory()->create();
$new_dir_name = Workspace::directory();
var_dump( rename($dir_name, $new_dir_name) );
//ensure that $new_dir_name got created
var_dump( file_exists($dir_name) );  // expecting false
var_dump( file_exists($new_dir_name) );  // expecting true

// try to rename an non_existing dir
echo "\n*** Testing rename() on non-existing directory ***\n";
$non_existent_dir_name = Workspace::directory();
$new_dir_name = Workspace::directory();
var_dump( rename($non_existent_dir_name, $new_dir_name) );
// ensure that $new_dir_name didn't get created
var_dump( file_exists($non_existent_dir_name) );  // expecting flase
var_dump( file_exists($new_dir_name) );  // expecting false

echo "Done\n";
?>
--EXPECTF--
*** Testing rename() on non-existing file ***

Warning: rename(): The system cannot find the file specified (code: 2) in %s on line %d
bool(false)
bool(false)
bool(false)

*** Testing rename() on existing directory ***
bool(true)
bool(false)
bool(true)

*** Testing rename() on non-existing directory ***

Warning: rename(): The system cannot find the file specified (code: 2) in %s on line %d
bool(false)
bool(false)
bool(false)
Done
