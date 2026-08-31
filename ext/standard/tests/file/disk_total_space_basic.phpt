--TEST--
Test disk_total_space() function : basic functionality
--CONFLICTS--
disk_total_space
--FILE--
<?php
echo "*** Testing with normal directory ***\n";
var_dump( disk_total_space(Workspace::path()) );

echo "*** Testing with newly created directory ***\n";
$dir = Workspace::directory()->create();

var_dump( disk_total_space($dir) );
$fh = fopen($dir->file(), "w");
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
