--TEST--
Test stat() functions: usage variations - effects of is_dir() & is_file()
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) != 'WIN') {
    die('skip.. only for Windows');
}
?>
--FILE--
<?php

/* test the effects on the stats of dir/file for using is_dir() & is_file() on dir/file */

require __DIR__ . "/file.inc";


/* create temp file and directory */
$old_dirname = TestPath::directory();
mkdir($old_dirname);  // temp dir

$old_filename = TestPath::file();
$file_handle = fopen($old_filename, "w");  // temp file
fclose($file_handle);


echo "\n*** Testing stat(): on file and directory after accessing it
    with is_dir() and is_file() functions ***\n";

// is_dir() on a directory
echo "-- Testing on Directory --\n";
$old_stat = stat($old_dirname);
// clear the cache
clearstatcache();
sleep(1);
var_dump( is_dir($old_dirname) );
$new_stat = stat($old_dirname);

// compare self stats
var_dump( compare_self_stat($old_stat) );
var_dump( compare_self_stat($new_stat) );
// compare the stat
var_dump( compare_stats($old_stat, $new_stat, $all_stat_keys, "==") );
// clear the stat
clearstatcache();


// is_file() on a file
echo "-- Testing on file --\n";
$old_stat = stat($old_filename);
// clear the stat
clearstatcache();
sleep(2);
var_dump( is_file($old_filename) );
$new_stat = stat($old_filename);
// compare self stats
var_dump( compare_self_stat($old_stat) );
var_dump( compare_self_stat($new_stat) );
// compare the stat
var_dump( compare_stats($old_stat, $new_stat, $all_stat_keys, "==") );
// clear the stat
clearstatcache();

echo "\n*** Done ***";
?>
--EXPECT--
*** Testing stat(): on file and directory after accessing it
    with is_dir() and is_file() functions ***
-- Testing on Directory --
bool(true)
bool(true)
bool(true)
bool(true)
-- Testing on file --
bool(true)
bool(true)
bool(true)
bool(true)

*** Done ***
