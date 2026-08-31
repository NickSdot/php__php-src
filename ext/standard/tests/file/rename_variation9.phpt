--TEST--
Test rename() function: basic functionality
--FILE--
<?php
echo "\n*** Testing rename() by giving stream context as third argument ***\n";
$context = stream_context_create();

// on directory
$dir_name = Workspace::directory()->create();
$new_dir_name = Workspace::directory();

var_dump( rename($dir_name, $new_dir_name, $context) );
var_dump( file_exists($dir_name) );  // expecting flase
var_dump( file_exists($new_dir_name) ); // expecting true

//on file
$src_name = Workspace::file();
$dest_name = Workspace::file();

// create the file
$fp = fopen($src_name, "w");
$s1 = stat($src_name);
fclose($fp);

var_dump( rename($src_name, $dest_name, $context) );
var_dump( file_exists($src_name) );  // expecting false
var_dump( file_exists($dest_name) );  // expecting true

echo "Done\n";
?>
--EXPECT--
*** Testing rename() by giving stream context as third argument ***
bool(true)
bool(false)
bool(true)
bool(true)
bool(false)
bool(true)
Done
