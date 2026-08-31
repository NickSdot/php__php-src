--TEST--
Test readlink() and realpath() functions: usage variation - linkname/filename stored in array(Bug #42038)
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) != 'WIN') {
    die('skip only for Windows');
} else {
    include __DIR__ . '/windows_links/common.inc';
    skipIfSeCreateSymbolicLinkPrivilegeIsDisabled(__FILE__);
}
?>
--FILE--
<?php
echo "*** Testing readlink() and realpath() : usage variations ***\n";
// create temp dir
$name_prefix = Workspace::directory();
$root = $name_prefix->directory('readlink_realpath_variation2');
$home = $root->directory('home');
$tests = $home->directory('tests');
$link_dir = $tests->directory('link');
mkdir($link_dir, 0777, true);
// create the file
$filename = $link_dir->file('readlink_realpath_variation2.tmp');
$link1 = $link_dir->file('readlink_realpath_variation2_link.tmp');
$outside_home = $name_prefix->directory('home');
$link2 = $outside_home->file('readlink_realpath_variation2_link.tmp');
$fp = fopen($filename, "w");
fclose($fp);

echo "\n*** Testing readlink() and realpath() with linkname stored in an array ***\n";
$link_arr = array (
  "$name_prefix////readlink_realpath_variation2/home/tests/link/readlink_realpath_variation2_link.tmp",
  "$name_prefix/./readlink_realpath_variation2/home/../home//tests//..//..//..//home//readlink_realpath_variation2_link.tmp/"
);

echo "\n-- Testing readlink() and realpath() with softlink, linkname stored inside an array --\n";
// creating the links
var_dump( symlink($filename, $link_arr[0]) );
var_dump( readlink($link_arr[0]) );
var_dump( realpath($link_arr[0]) );
var_dump( symlink($filename, $link_arr[1]) );
var_dump( readlink($link_arr[1]) );
var_dump( realpath($link_arr[1]) );

// deleting the link
unlink($link_arr[0]);
unlink($link_arr[1]);

echo "\n-- Testing readlink() and realpath() with hardlink, linkname stored inside an array --\n";
// creating hard links
var_dump( link($filename, $link_arr[0]) );
var_dump( readlink($link_arr[0]) );   // invalid because readlink doesn't work with hardlink
var_dump( realpath($link_arr[0]) );
var_dump( link($filename, $link_arr[1]) );
var_dump( readlink($link_arr[1]) );   // invalid because readlink doesn't work with hardlink
var_dump( realpath($link_arr[1]) );

// delete the links
unlink($link_arr[0]);
unlink($link_arr[1]);

echo "Done\n";
?>
--EXPECTF--
*** Testing readlink() and realpath() : usage variations ***

*** Testing readlink() and realpath() with linkname stored in an array ***

-- Testing readlink() and realpath() with softlink, linkname stored inside an array --
bool(true)
string(%d) "%s%ereadlink_realpath_variation2%ehome%etests%elink%ereadlink_realpath_variation2.tmp"
string(%d) "%s%ereadlink_realpath_variation2%ehome%etests%elink%ereadlink_realpath_variation2.tmp"

Warning: symlink(): No such file or directory in %s on line %d
bool(false)

Warning: readlink(): readlink failed to read the symbolic link (%s), error %d in %s on line %d
bool(false)
bool(false)

Warning: unlink(): No such file or directory in %s on line %d

-- Testing readlink() and realpath() with hardlink, linkname stored inside an array --
bool(true)
string(%d) "%s%ereadlink_realpath_variation2%ehome%etests%elink%ereadlink_realpath_variation2_link.tmp"
string(%d) "%s%ereadlink_realpath_variation2%ehome%etests%elink%ereadlink_realpath_variation2_link.tmp"

Warning: link(): No such file or directory in %s on line %d
bool(false)

Warning: readlink(): readlink failed to read the symbolic link (%s), error %d in %s on line %d
bool(false)
bool(false)

Warning: unlink(): No such file or directory in %s on line %d
Done
