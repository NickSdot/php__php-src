--TEST--
Test copy() function: usage variations - dir as source (Bug #42111)
--SKIPIF--
<?php
if(substr(PHP_OS, 0, 3) == "WIN")
  die("skip Do not run on Windows");
?>
--FILE--
<?php
/* Test copy(): Trying to create a copy of an existing dir */

echo "*** Test copy() function: Trying to create a copy of an existing dir ***\n";
$src_dir = TestPath::directory()->create();
$dest = TestPath::file();

var_dump( copy($src_dir, $dest) );

var_dump( file_exists($dest) );

var_dump( filesize($src_dir) );
var_dump( filesize($dest) );

echo "*** Done ***\n";
?>
--EXPECTF--
*** Test copy() function: Trying to create a copy of an existing dir ***

Warning: copy(): The first argument to copy() function cannot be a directory in %scopy_variation12.php on line %d
bool(false)
bool(false)
int(%d)

Warning: filesize(): stat failed for %scopy_copy_variation12 in %scopy_variation12.php on line %d
bool(false)
*** Done ***
