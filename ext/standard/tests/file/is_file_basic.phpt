--TEST--
Test is_file() function: basic functionality
--FILE--
<?php
$file = TestPath::file();
echo "*** Testing is_file(): basic functionality ***\n";

/* Checking with current file */
var_dump( is_file(__FILE__) );

/* Checking with (current) dir */
var_dump( is_file(__DIR__) );

/* With non-existing file */
var_dump( is_file($file) );
/* With existing file */
fclose( fopen($file, "w") );
var_dump( is_file($file) );

echo "*** Testing is_file() for its return value type ***\n";
var_dump( is_bool( is_file(__FILE__) ) );
var_dump( is_bool( is_file("/no/such/file") ) );

echo "\n*** Done ***";
?>
--EXPECT--
*** Testing is_file(): basic functionality ***
bool(true)
bool(false)
bool(false)
bool(true)
*** Testing is_file() for its return value type ***
bool(true)
bool(true)

*** Done ***
