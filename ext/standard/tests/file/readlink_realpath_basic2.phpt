--TEST--
Test readlink() and realpath functions: basic functionality - diff. path notation for files
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) == 'WIN') {
    die('skip not for Windows');
}
?>
--FILE--
<?php
/* creating directories, symbolic links and files */
$file_path = Workspace::directory();
$root = $file_path->directory('readlink_realpath_basic2');
$home = $root->directory('home');
$test = $home->directory('test');
mkdir($test, 0777, true);

$file1 = $test->file('readlink_realpath_basic2.tmp');
$file2 = $home->file('readlink_realpath_basic2.tmp');
$file3 = $root->file('readlink_realpath_basic2.tmp');
$file_handle1 = fopen($file1, "w");
$file_handle2 = fopen($file2, "w");
$file_handle3 = fopen($file3, "w");
fclose($file_handle1);
fclose($file_handle2);
fclose($file_handle3);

echo "\n*** Testing realpath() on filenames ***\n";
$filenames = array (
  /* filenames resulting in valid paths */
  "$file_path/readlink_realpath_basic2/home/readlink_realpath_basic2.tmp",
  "$file_path/readlink_realpath_basic2/readlink_realpath_basic2.tmp",
  "$file_path/readlink_realpath_basic2//home/test//../test/./readlink_realpath_basic2.tmp",
  "$file_path/readlink_realpath_basic2/home//../././readlink_realpath_basic2.tmp",

  /* filenames with invalid path */
  "$file_path///readlink_realpath_basic2/home//..//././test//readlink_realpath_basic2.tmp",
  "$file_path/readlink_realpath_basic2/home/../home/../test/../readlink_realpath_basic2.tmp",
  "$file_path/readlink_realpath_basic2/readlink_realpath_basic2.tmp/"
);

$counter = 1;
/* loop through $files to read the filepath of $file in the above array */
foreach($filenames as $file) {
  echo "\n-- Iteration $counter --\n";
  var_dump( realpath($file) );
  $counter++;
}

echo "Done\n";
?>
--EXPECTF--
*** Testing realpath() on filenames ***

-- Iteration 1 --
string(%d) "%s/readlink_realpath_basic2/home/readlink_realpath_basic2.tmp"

-- Iteration 2 --
string(%d) "%s/readlink_realpath_basic2/readlink_realpath_basic2.tmp"

-- Iteration 3 --
string(%d) "%s/readlink_realpath_basic2/home/test/readlink_realpath_basic2.tmp"

-- Iteration 4 --
string(%d) "%s/readlink_realpath_basic2/readlink_realpath_basic2.tmp"

-- Iteration 5 --
bool(false)

-- Iteration 6 --
bool(false)

-- Iteration 7 --
%s
Done
