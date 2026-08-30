--TEST--
Test md5_file() function with ASCII output and raw binary output
--FILE--
<?php

/* Creating an empty file */
$emptyFile = TestPath::file();
if (($handle = fopen($emptyFile, "w+")) == FALSE)
return false;

/* Creating a data file */
$dataFile = TestPath::file();
if (($handle2 = fopen($dataFile, "w+")) == FALSE)
return false;

/* Writing into file */
$filename = $dataFile;
$content = "Add this to the file\n";
if (is_writable($filename)) {
  if (fwrite($handle2, $content) === FALSE) {
    echo "Cannot write to file ($filename)";
    exit;
  }
}

// close the files
fclose($handle);
fclose($handle2);

/* Testing error conditions */
echo "\n*** Testing for error conditions ***\n";

/* No filename */
try {
    var_dump( md5_file("") );
} catch (\ValueError $e) {
    echo $e->getMessage() . \PHP_EOL;
}
/* invalid filename */
var_dump( md5_file("aZrq16u") );

/* Scalar value as filename  */
var_dump( md5_file(12) );

/* Hexadecimal Output for Empty file as input */
echo "\n*** Hexadecimal Output for Empty file as Argument ***\n";
var_dump( md5_file($emptyFile) );

/* Raw Binary Output for Empty file as input */
echo "\n*** Raw Binary Output for Empty file as Argument ***\n";
var_dump( md5_file($emptyFile, true) );

/* Normal operation with hexadecimal output */
echo "\n*** Hexadecimal Output for a valid file with some contents ***\n";
var_dump( md5_file($dataFile) );

/* Normal operation with raw binary output */
echo "\n*** Raw Binary Output for a valid file with some contents ***\n";
var_dump ( md5_file($dataFile, true) );


echo "\nDone";
?>
--EXPECTF--
*** Testing for error conditions ***
Path must not be empty

Warning: md5_file(): Failed to open stream: No such file or directory in %s on line %d
bool(false)

Warning: md5_file(): Failed to open stream: No such file or directory in %s on line %d
bool(false)

*** Hexadecimal Output for Empty file as Argument ***
string(32) "d41d8cd98f00b204e9800998ecf8427e"

*** Raw Binary Output for Empty file as Argument ***
string(16) "‘åŸè%0≤ÈÄ	òÏ¯B~"

*** Hexadecimal Output for a valid file with some contents ***
string(32) "7f28ec647825e2a70bf67778472cd4a2"

*** Raw Binary Output for a valid file with some contents ***
string(16) "(Ïdx%‚ßˆwxG,‘¢"

Done
