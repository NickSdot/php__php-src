--TEST--
Test lstat() and stat() functions: usage variations - effects of rename() on file
--FILE--
<?php
/* test the effects of rename() on stats of file */

$file_path = __DIR__;
require "$file_path/file.inc";
$old_file = TestPath::file();
$new_file = TestPath::file();

/* create temp file */
$fp = fopen($old_file->path, "w");  // temp file
fclose($fp);

// renaming a file and check stat
echo "*** Testing stat() for files after being renamed ***\n";
$file_path = __DIR__;
$old_filename = $old_file->path;
$new_filename = $new_file->path;
$old_stat = stat($old_filename);
clearstatcache();
var_dump( rename($old_filename, $new_filename) );
$new_stat = stat($new_filename);

// compare the self stat
var_dump( compare_self_stat($old_stat) );
var_dump( compare_self_stat($new_stat) );

// compare the two stats
var_dump( compare_stats($old_stat, $old_stat, $all_stat_keys) );
// clear the cache
clearstatcache();

echo "\n--- Done ---";
?>
--EXPECT--
*** Testing stat() for files after being renamed ***
bool(true)
bool(true)
bool(true)
bool(true)

--- Done ---
