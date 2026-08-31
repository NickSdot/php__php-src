--TEST--
Test readlink() and realpath functions: basic functionality - diff. path notation for links(Bug #42038)
--SKIPIF--
<?php
if (PHP_OS_FAMILY === 'Windows') {
    include __DIR__ . '/windows_links/common.inc';
    skipIfSeCreateSymbolicLinkPrivilegeIsDisabled(__FILE__);
}
?>
--FILE--
<?php
/* creating directories, symbolic links and files */
$file_path = Workspace::directory();
$root = $file_path->directory('readlink_realpath_basic1');
$home = $root->directory('home');
$test = $home->directory('test');
mkdir($test, 0777, true);

$file1 = $test->file('readlink_realpath_basic1.tmp');
$file2 = $home->file('readlink_realpath_basic1.tmp');
$file3 = $root->file('readlink_realpath_basic1.tmp');
$link1 = $test->file('readlink_realpath_basic1_link.tmp');
$link2 = $home->file('readlink_realpath_basic1_link.tmp');
$file_handle1 = fopen($file1, "w");
$file_handle2 = fopen($file2, "w");
$file_handle3 = fopen($file3, "w");
fclose($file_handle1);
fclose($file_handle2);
fclose($file_handle3);

symlink($file1, $link1);
symlink($file2, $link2);


echo "*** Testing readlink() and realpath(): with valid and invalid path ***\n";
$linknames = array (
  /* linknames resulting in valid paths */
  "$file_path/readlink_realpath_basic1/home/readlink_realpath_basic1_link.tmp",
  "$file_path/readlink_realpath_basic1/home/test/readlink_realpath_basic1_link.tmp",
  "$file_path/readlink_realpath_basic1//home/test//../test/./readlink_realpath_basic1_link.tmp",

  /* linknames with invalid linkpath */
  "$file_path///readlink_realpath_basic1/home//..//././test//readlink_realpath_basic1_link.tmp",
  "$file_path/readlink_realpath_basic1/home/../home/../test/../readlink_realpath_basic1_link.tmp",
  "$file_path/readlink_realpath_basic1/..readlink_realpath_basic1_link.tmp",
  "$file_path/readlink_realpath_basic1/home/test/readlink_realpath_basic1_link.tmp/"
);

$counter = 1;
/* loop through $files to read the linkpath of
   the link created from each $file in the above array */
foreach($linknames as $link) {
  echo "\n-- Iteration $counter --\n";
  var_dump( readlink($link) );
  var_dump( realpath($link) );
  $counter++;
}

echo "Done\n";
?>
--EXPECTF--
*** Testing readlink() and realpath(): with valid and invalid path ***

-- Iteration 1 --
string(%d) "%s%ereadlink_realpath_basic1%ehome%ereadlink_realpath_basic1.tmp"
string(%d) "%s%ereadlink_realpath_basic1%ehome%ereadlink_realpath_basic1.tmp"

-- Iteration 2 --
string(%d) "%s%ereadlink_realpath_basic1%ehome%etest%ereadlink_realpath_basic1.tmp"
string(%d) "%s%ereadlink_realpath_basic1%ehome%etest%ereadlink_realpath_basic1.tmp"

-- Iteration 3 --
string(%d) "%s%ereadlink_realpath_basic1%ehome%etest%ereadlink_realpath_basic1.tmp"
string(%d) "%s%ereadlink_realpath_basic1%ehome%etest%ereadlink_realpath_basic1.tmp"

-- Iteration 4 --

Warning: readlink(): %s in %s on line %d
bool(false)
bool(false)

-- Iteration 5 --

Warning: readlink(): %s in %s on line %d
bool(false)
bool(false)

-- Iteration 6 --

Warning: readlink(): %s in %s on line %d
bool(false)
%s

-- Iteration 7 --

Warning: readlink(): %s in %s on line %d
bool(false)
%s
Done
