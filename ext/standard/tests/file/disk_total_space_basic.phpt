--TEST--
Test disk_total_space() function : basic functionality
--CONFLICTS--
disk_total_space
--FILE--
<?php
$directory = TestPath::directory();
$file = $directory->file();
$file_path = __DIR__;

echo "*** Testing with normal directory ***\n";
var_dump( disk_total_space($file_path) );

echo "*** Testing with newly created directory ***\n";
$dir = $directory;

mkdir($dir);
var_dump( disk_total_space($dir) );
$fh = fopen($file->path, "w");
fwrite($fh, "Garbage Data Garbage Data Garbage Data Garbage Data Garbage Data Garbage Data Garbage Data");

fclose($fh);

echo"\nTotal Space after writing to a file\n";
var_dump( disk_total_space($dir) );

echo"\n-- Done --";
?>
--EXPECTF--
*** Testing with normal directory ***
float(%f)
*** Testing with newly created directory ***
float(%f)

Total Space after writing to a file
float(%f)

-- Done --
