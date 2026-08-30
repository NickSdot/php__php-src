--TEST--
Test dir() function : usage variations - relative valid and invalid paths
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) == 'WIN') {
    die('skip.. Not valid for Windows');
}
?>
--FILE--
<?php
/*
 * Checking the behavior of dir() function by passing directories which
 * have valid and invalid relative path.
 */

echo "*** Testing dir() : checking with valid and invalid paths ***\n";

/* create the temporary directories */

TestContext::useRelativeWorkspace();
$root = TestPath::directory();
$file_path = $root->path;
@mkdir($file_path);

// directory dir_variation91 with one sub-directory sub_dir11 and sub-sub-directory sub_dir111
$dir1 = $root->directory('dir_variation91');
$dir_path1 = $dir1->path;
$sub1 = $dir1->directory('sub_dir11');
$sub_dir11 = $sub1->path;
$sub_dir111 = $sub1->directory('sub_dir111')->path;

// directory dir_variation92 with one sub-directory sub_dir21
$dir2 = $root->directory('dir_variation92');
$dir_path2 = $dir2->path;
$sub_dir21 = $dir2->directory('sub_dir21')->path;

@mkdir($dir_path1);
@mkdir($dir_path2);
@mkdir($sub_dir11);
@mkdir($sub_dir111);
@mkdir($sub_dir21);

// open the directory with valid paths
echo "\n-- With valid paths --\n";
var_dump( dir("$dir_path1/sub_dir11/sub_dir111/..") );
var_dump( dir("$dir_path2/sub_dir21/../../dir_variation91") );
var_dump( dir("$dir_path2/sub_dir21/../../dir_variation91/sub_dir11/..") );
var_dump( dir("$dir_path1/sub_dir11/sub_dir111/../../../dir_variation92/sub_dir21/..") );

// open the directory with invalid path
echo "\n-- With invalid paths --\n";
var_dump( dir("$dir_path1/sub_dir12/sub_dir111/..") );
var_dump( dir("$dir_path2/sub_dir21/../dir_variation91") );
var_dump( dir("$dir_path2/sub_dir21/../../dir_variation91/sub_dir12/..") );
var_dump( dir("$dir_path1/sub_dir11/sub_dir111/../../dir_variation92/sub_dir21/..") );

echo "Done";
?>
--EXPECTF--
*** Testing dir() : checking with valid and invalid paths ***

-- With valid paths --
object(Directory)#%d (2) {
  ["path"]=>
  string(%d) "%s/dir_variation91/sub_dir11/sub_dir111/.."
  ["handle"]=>
  resource(%d) of type (stream)
}
object(Directory)#%d (2) {
  ["path"]=>
  string(%d) "%s/dir_variation92/sub_dir21/../../dir_variation91"
  ["handle"]=>
  resource(%d) of type (stream)
}
object(Directory)#%d (2) {
  ["path"]=>
  string(%d) "%s/dir_variation92/sub_dir21/../../dir_variation91/sub_dir11/.."
  ["handle"]=>
  resource(%d) of type (stream)
}
object(Directory)#%d (2) {
  ["path"]=>
  string(%d) "%s/dir_variation91/sub_dir11/sub_dir111/../../../dir_variation92/sub_dir21/.."
  ["handle"]=>
  resource(%d) of type (stream)
}

-- With invalid paths --

Warning: dir(): Failed to open directory: %s in %s on line %d
bool(false)

Warning: dir(): Failed to open directory: %s in %s on line %d
bool(false)

Warning: dir(): Failed to open directory: %s in %s on line %d
bool(false)

Warning: dir(): Failed to open directory: %s in %s on line %d
bool(false)
Done
