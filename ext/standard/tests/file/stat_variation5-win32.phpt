--TEST--
Test stat() functions: usage variations - file opened in read/write mode
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) != 'WIN') {
    die('skip.. only for Windows');
}
?>
--FILE--
<?php

/* test the stats of file opened in write mode and then same in read mode */

require __DIR__ . "/file.inc";


$filename = Workspace::file();
$file_handle = fopen($filename, "w");  // temp file
fclose($file_handle);


echo "\n*** Testing stat(): on a file with read/write permission ***\n";

$file_handle = fopen($filename, "w");  // create file
fclose($file_handle);
$old_stat = stat($filename);
// clear the stat
clearstatcache();
sleep(1);
// opening file again in read mode
$file_handle = fopen($filename, "r");  // read file
fclose($file_handle);
$new_stat = stat($filename);
// compare self stats
var_dump( compare_self_stat($old_stat) );
var_dump( compare_self_stat($new_stat) );
// compare the stat
$affected_members = array(10, 'ctime');
var_dump( compare_stats($old_stat, $new_stat, $affected_members, "==") );
// clear the stat
clearstatcache();


echo "\n*** Done ***";
?>
--EXPECT--
*** Testing stat(): on a file with read/write permission ***
bool(true)
bool(true)
bool(true)

*** Done ***
