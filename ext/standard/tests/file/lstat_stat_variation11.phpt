--TEST--
Test lstat() and stat() functions: usage variations - effect of is_file()
--SKIPIF--
<?php
if (getenv("SKIP_SLOW_TESTS")) die("skip slow test");
?>
--FILE--
<?php
$file_path = __DIR__;
require "$file_path/file.inc";
$file = TestPath::file();

/* test the effects of is_file() on stats of a file */

/* create temp file */
$filename = $file->path;
$fp = fopen($filename, "w");  // temp file
fclose($fp);

// is_file() on a file
echo "*** Testing stat() on a file after using is_file() on it ***\n";
$old_stat = stat($filename);
// clear the stat
clearstatcache();
sleep(1);
var_dump( is_file($filename) );
$new_stat = stat($filename);
// compare self stats
var_dump( compare_self_stat($old_stat) );
var_dump( compare_self_stat($new_stat) );
// compare the stat
var_dump( compare_stats($old_stat, $new_stat, $all_stat_keys) );
// clear the stat
clearstatcache();

echo "\n--- Done ---";
?>
--EXPECT--
*** Testing stat() on a file after using is_file() on it ***
bool(true)
bool(true)
bool(true)
bool(true)

--- Done ---
