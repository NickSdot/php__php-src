--TEST--
Test filesize() function: basic functionaity
--FILE--
<?php
echo "*** Testing size of files and directories with filesize() ***\n";

$file_path = TestPath::directory()->path;
mkdir($file_path);

var_dump( filesize(__FILE__) );
var_dump( filesize(".") );

/* Empty file */
$file_name = $file_path."/filesize_basic.tmp";
$file_handle = fopen($file_name, "w");
fclose($file_handle);
var_dump( filesize($file_name) );

echo "*** Done ***\n";
?>
--EXPECTF--
*** Testing size of files and directories with filesize() ***
int(%d)
int(%d)
int(0)
*** Done ***
