--TEST--
Test filesize() function: basic functionaity
--FILE--
<?php
echo "*** Testing size of files and directories with filesize() ***\n";

var_dump( filesize(__FILE__) );
var_dump( filesize(".") );

/* Empty file */
$file_name = Workspace::file()->create();
var_dump( filesize($file_name) );

echo "*** Done ***\n";
?>
--EXPECTF--
*** Testing size of files and directories with filesize() ***
int(%d)
int(%d)
int(0)
*** Done ***
