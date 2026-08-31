--TEST--
Test lstat() and stat() functions: usage variations - effects of rename() on link
--SKIPIF--
<?php
if (PHP_OS_FAMILY === 'Windows') {
    include_once __DIR__ . '/windows_links/common.inc';
    skipIfSeCreateSymbolicLinkPrivilegeIsDisabled(__FILE__);
}
?>
--FILE--
<?php
/* test the effects of rename() on stats of link */

$file_path = __DIR__;
require "$file_path/file.inc";

/* create temp file & link */
$tmp_file = Workspace::file();
$old_linkname = Workspace::file();
$new_linkname = Workspace::file();
$fp = fopen($tmp_file, "w");  // temp file
fclose($fp);

// temp link
symlink($tmp_file, $old_linkname);

// renaming a link
echo "*** Testing lstat() for link after being renamed ***\n";
$old_stat = lstat($old_linkname);
clearstatcache();
var_dump( rename($old_linkname, $new_linkname) );
$new_stat = lstat($new_linkname);

// compare self stats
var_dump( compare_self_stat($old_stat) );
var_dump( compare_self_stat($new_stat) );

// compare the two stats - all except ctime
$keys_to_compare = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12,
                       "dev", "ino", "mode", "nlink", "uid", "gid",
                       "rdev", "size", "atime", "mtime", "blksize", "blocks");
var_dump( compare_stats($old_stat, $new_stat, $keys_to_compare) );
?>
--EXPECT--
*** Testing lstat() for link after being renamed ***
bool(true)
bool(true)
bool(true)
bool(true)
