--TEST--
Test file_put_contents() and file_get_contents() functions : basic functionality
--FILE--
<?php

include(__DIR__."/file.inc");

echo "*** Testing the basic functionality of file_put_contents() and file_get_contents() functions ***\n";

echo "-- Testing with simple valid data file --\n";

$file_name = Workspace::file()->path;
fill_buffer($text_buffer, "text", 100);
file_put_contents( $file_name, $text_buffer );

var_dump( file_get_contents($file_name) );

echo "\n-- Testing with empty file --\n";

$file_name = Workspace::file()->path;
file_put_contents( $file_name, "");
var_dump( file_get_contents( $file_name ) );

echo "\n*** Done ***";
?>
--EXPECT--
*** Testing the basic functionality of file_put_contents() and file_get_contents() functions ***
-- Testing with simple valid data file --
string(100) "text text text text text text text text text text text text text text text text text text text text "

-- Testing with empty file --
string(0) ""

*** Done ***
