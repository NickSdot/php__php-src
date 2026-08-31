--TEST--
Test readfile() function: usage variation - include path
--FILE--
<?php
/* test readfile() by providing an include path, second argument */

// include file.inc
require("file.inc");

$directory = Workspace::directory()->create();

echo "*** Testing readfile(): checking second argument, include path ***\n";
// temp file name used here
$file = $directory->file();
// create file
$fp = fopen($file, "w");
fill_file($fp, "text_with_new_line", 50);
fclose($fp);

// including $directory in 'include_path'
ini_set('include_path', (string) $directory);
// 'include_path' set to true
$count = readfile($file->name, true);
echo "\n";
var_dump($count);
// use the context argument with include path
echo "*** Testing readfile(): checking second argument, include path with context specified ***\n";
$context = stream_context_create();
$count = readfile($file->name, true, $context);
echo "\n";
var_dump($count);

echo "Done\n";
?>
--EXPECT--
*** Testing readfile(): checking second argument, include path ***
line
line of text
line
line of text
line
line of t
int(50)
*** Testing readfile(): checking second argument, include path with context specified ***
line
line of text
line
line of text
line
line of t
int(50)
Done
