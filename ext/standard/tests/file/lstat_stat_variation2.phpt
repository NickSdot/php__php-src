--TEST--
Test lstat() and stat() functions: usage variations - effects of rename() on dir
--FILE--
<?php
/* test the effects of rename() on stats of dir */

require("file.inc");

// renaming a directory and check stat
echo "*** Testing stat() for directory after being renamed ***\n";
$old_dirname = Workspace::directory()->create();  // temp dir
$new_dirname = Workspace::directory();
$old_stat = stat($old_dirname);
clearstatcache();
var_dump( rename($old_dirname, $new_dirname) );
$new_stat = stat($new_dirname);

// compare self stats
var_dump( compare_self_stat($old_stat) );
var_dump( compare_self_stat($new_stat) );

// compare the two stats - all except ctime
$keys_to_compare = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12,
                       "dev", "ino", "mode", "nlink", "uid", "gid",
                       "rdev", "size", "atime", "mtime", "blksize", "blocks");
var_dump( compare_stats($old_stat, $new_stat, $keys_to_compare) );
// clear the cache
clearstatcache();

echo "\n--- Done ---";
?>
--EXPECT--
*** Testing stat() for directory after being renamed ***
bool(true)
bool(true)
bool(true)
bool(true)

--- Done ---
