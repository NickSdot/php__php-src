--TEST--
Test fileowner() function: basic functionality
--FILE--
<?php
$file = TestPath::file();
$directory = TestPath::directory();
echo "*** Testing fileowner(): basic functionality ***\n";

echo "-- Testing with the file or directory created by owner --\n";
var_dump( fileowner(__FILE__) );
var_dump( fileowner(".") );
var_dump( fileowner("./..") );

/* Newly created files and dirs */
$file_handle = fopen($file, "w");
$string = "Hello, world\n1234\n123Hello";
fwrite($file_handle, $string);
var_dump( fileowner($file) );
fclose($file_handle);

$directory->create();
var_dump( fileowner($directory) );

echo "*** Done ***\n";
?>
--EXPECTF--
*** Testing fileowner(): basic functionality ***
-- Testing with the file or directory created by owner --
int(%d)
int(%d)
int(%d)
int(%d)
int(%d)
*** Done ***
