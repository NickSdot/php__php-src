--TEST--
Test stat() functions: usage variations - names of dir/file stored in objects
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) != 'WIN') {
    die('skip.. only for Windows');
}
?>
--FILE--
<?php

/* test the stats of dir/file when their names are stored in objects */

require __DIR__ . "/file.inc";


$filename_a = Workspace::file();
$filename_b = Workspace::file();

$file_handle = fopen($filename_a, "w");  // temp file
fclose($file_handle);


echo "\n*** Testing stat(): with filename
    and directory name stored inside a object ***\n";

// creating object with members as numeric and non-numeric filename and directory name
class object_temp {
public $var_name;
public function __construct($name) {
$this->var_name = $name;
  }
}

/* create directories */
$dirname_a = Workspace::directory()->create();
$dirname_b = Workspace::directory()->create();

// directory as member
$obj1 = new object_temp($dirname_a . '/');
$obj2 = new object_temp($dirname_b . '/');

// file as member
$obj3 = new object_temp($filename_a->path);
$obj4 = new object_temp($filename_b->path);

echo "\n-- Testing stat() on filename stored inside an object --\n";
var_dump( stat($obj3->var_name) );

$file_handle = fopen($filename_b, "w");
fclose($file_handle);
var_dump( stat($obj4->var_name) );

echo "\n-- Testing stat() on directory name stored inside an object --\n";
var_dump( stat($obj1->var_name) );
var_dump( stat($obj2->var_name) );

echo "\n*** Done ***";
?>
--EXPECTF--
*** Testing stat(): with filename
    and directory name stored inside a object ***

-- Testing stat() on filename stored inside an object --
array(26) {
  [0]=>
  int(%i)
  [1]=>
  int(%d)
  [2]=>
  int(%d)
  [3]=>
  int(%d)
  [4]=>
  int(%d)
  [5]=>
  int(%d)
  [6]=>
  int(%d)
  [7]=>
  int(%d)
  [8]=>
  int(%d)
  [9]=>
  int(%d)
  [10]=>
  int(%d)
  [11]=>
  int(-%d)
  [12]=>
  int(-%d)
  ["dev"]=>
  int(%i)
  ["ino"]=>
  int(%d)
  ["mode"]=>
  int(%d)
  ["nlink"]=>
  int(%d)
  ["uid"]=>
  int(%d)
  ["gid"]=>
  int(%d)
  ["rdev"]=>
  int(%d)
  ["size"]=>
  int(%d)
  ["atime"]=>
  int(%d)
  ["mtime"]=>
  int(%d)
  ["ctime"]=>
  int(%d)
  ["blksize"]=>
  int(-%d)
  ["blocks"]=>
  int(-%d)
}
array(26) {
  [0]=>
  int(%i)
  [1]=>
  int(%d)
  [2]=>
  int(%d)
  [3]=>
  int(%d)
  [4]=>
  int(%d)
  [5]=>
  int(%d)
  [6]=>
  int(%d)
  [7]=>
  int(%d)
  [8]=>
  int(%d)
  [9]=>
  int(%d)
  [10]=>
  int(%d)
  [11]=>
  int(-%d)
  [12]=>
  int(-%d)
  ["dev"]=>
  int(%i)
  ["ino"]=>
  int(%d)
  ["mode"]=>
  int(%d)
  ["nlink"]=>
  int(%d)
  ["uid"]=>
  int(%d)
  ["gid"]=>
  int(%d)
  ["rdev"]=>
  int(%d)
  ["size"]=>
  int(%d)
  ["atime"]=>
  int(%d)
  ["mtime"]=>
  int(%d)
  ["ctime"]=>
  int(%d)
  ["blksize"]=>
  int(-%d)
  ["blocks"]=>
  int(-%d)
}

-- Testing stat() on directory name stored inside an object --
array(26) {
  [0]=>
  int(%i)
  [1]=>
  int(%d)
  [2]=>
  int(%d)
  [3]=>
  int(%d)
  [4]=>
  int(%d)
  [5]=>
  int(%d)
  [6]=>
  int(%d)
  [7]=>
  int(%d)
  [8]=>
  int(%d)
  [9]=>
  int(%d)
  [10]=>
  int(%d)
  [11]=>
  int(-%d)
  [12]=>
  int(-%d)
  ["dev"]=>
  int(%i)
  ["ino"]=>
  int(%d)
  ["mode"]=>
  int(%d)
  ["nlink"]=>
  int(%d)
  ["uid"]=>
  int(%d)
  ["gid"]=>
  int(%d)
  ["rdev"]=>
  int(%d)
  ["size"]=>
  int(%d)
  ["atime"]=>
  int(%d)
  ["mtime"]=>
  int(%d)
  ["ctime"]=>
  int(%d)
  ["blksize"]=>
  int(-%d)
  ["blocks"]=>
  int(-%d)
}
array(26) {
  [0]=>
  int(%i)
  [1]=>
  int(%d)
  [2]=>
  int(%d)
  [3]=>
  int(%d)
  [4]=>
  int(%d)
  [5]=>
  int(%d)
  [6]=>
  int(%d)
  [7]=>
  int(%d)
  [8]=>
  int(%d)
  [9]=>
  int(%d)
  [10]=>
  int(%d)
  [11]=>
  int(-%d)
  [12]=>
  int(-%d)
  ["dev"]=>
  int(%i)
  ["ino"]=>
  int(%d)
  ["mode"]=>
  int(%d)
  ["nlink"]=>
  int(%d)
  ["uid"]=>
  int(%d)
  ["gid"]=>
  int(%d)
  ["rdev"]=>
  int(%d)
  ["size"]=>
  int(%d)
  ["atime"]=>
  int(%d)
  ["mtime"]=>
  int(%d)
  ["ctime"]=>
  int(%d)
  ["blksize"]=>
  int(-%d)
  ["blocks"]=>
  int(-%d)
}

*** Done ***
