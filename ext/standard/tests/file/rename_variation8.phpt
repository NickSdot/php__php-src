--TEST--
Test rename() function: variation
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) == 'WIN') die('skip..  not for Windows');
?>
--FILE--
<?php
echo "\n*** Testing rename() on non-existing file ***\n";
$source_file = TestPath::file();
$destination_file = TestPath::file();
$directory = TestPath::directory();
$renamed_directory = TestPath::directory();
$missing_directory = TestPath::directory();
$missing_destination = TestPath::directory();

// try renaming a non existing file
var_dump( rename($source_file, $destination_file) );

// ensure that $dest_name didn't get created
var_dump( file_exists($source_file) );  // expecting false
var_dump( file_exists($destination_file) ); // expecting false

// rename a existing dir to new name
echo "\n*** Testing rename() on existing directory ***\n";
mkdir($directory);
var_dump( rename($directory, $renamed_directory) );
//ensure that $new_dir_name got created
var_dump( file_exists($directory) );  // expecting false
var_dump( file_exists($renamed_directory) );  // expecting true

// try to rename an non_existing dir
echo "\n*** Testing rename() on non-existing directory ***\n";
var_dump( rename($missing_directory, $missing_destination) );
// ensure that $new_dir_name didn't get created
var_dump( file_exists($missing_directory) );  // expecting flase
var_dump( file_exists($missing_destination) );  // expecting false

echo "Done\n";
?>
--EXPECTF--
*** Testing rename() on non-existing file ***

Warning: rename(): No such file or directory in %s on line %d
bool(false)
bool(false)
bool(false)

*** Testing rename() on existing directory ***
bool(true)
bool(false)
bool(true)

*** Testing rename() on non-existing directory ***

Warning: rename(): No such file or directory in %s on line %d
bool(false)
bool(false)
bool(false)
Done
