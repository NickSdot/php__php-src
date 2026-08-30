--TEST--
Test lstat() and stat() functions: usage variations - effects changing permissions of dir
--SKIPIF--
<?php
if (getenv("SKIP_SLOW_TESTS")) die("skip slow test");
if (substr(PHP_OS, 0, 3) == 'WIN') {
    die('skip.. Not valid for Windows');
}
?>
--FILE--
<?php
/* test the effects on stats by changing permissions of a dir */

$file_path = __DIR__;
require "$file_path/file.inc";
$directory = TestPath::directory();

// checking stat() on directory
echo "*** Testing lstat() on a dir after changing its access permission ***\n";
$dirname = $directory->path;
mkdir($dirname);

$old_stat = stat($dirname);
sleep(1);
var_dump( chmod($dirname, 0777) );
// clear the stat
clearstatcache();
$new_stat = stat($dirname);
// compare self stats
var_dump( compare_self_stat($old_stat) );
var_dump( compare_self_stat($new_stat) );
// compare the stat
$affected_members = array(2, 10, 'mode', 'ctime');
var_dump( compare_stats($old_stat, $new_stat, $affected_members, "!=") );

echo "\n--- Done ---";
?>
--EXPECT--
*** Testing lstat() on a dir after changing its access permission ***
bool(true)
bool(true)
bool(true)
bool(true)

--- Done ---
