--TEST--
Test lstat() and stat() functions: usage variations - creating file/subdir
--SKIPIF--
<?php
if (getenv("SKIP_SLOW_TESTS")) die("skip slow test");
if (substr(PHP_OS, 0, 3) == 'WIN') {
    die('skip.. Not valid for Windows');
}
?>
--FILE--
<?php
require __DIR__ . "/file.inc";

/* test the effects on stats with creating file/subdir in a dir
*/

/* create temp file */
$dirname = Workspace::directory()->create();  // temp dir

// creating and deleting subdir and files in the dir
echo "*** Testing stat() on dir after subdir and file is created in it ***\n";
$old_stat = stat($dirname);
clearstatcache();
sleep(1);
$dirname->directory()->create();
$dirname->file()->create();
$new_stat = stat($dirname);

// compare self stats
var_dump( compare_self_stat($old_stat) );
var_dump( compare_self_stat($new_stat) );
// compare the stat
$affected_members = array(9, 10, 'mtime', 'ctime');
clearstatcache();
var_dump(compare_stats($old_stat, $new_stat, $affected_members, "<"));

echo "\n--- Done ---";
?>
--EXPECT--
*** Testing stat() on dir after subdir and file is created in it ***
bool(true)
bool(true)
bool(true)

--- Done ---
