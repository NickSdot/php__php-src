--TEST--
Test dir() function : usage variations - directories with restricted permissions
--SKIPIF--
<?php
if( substr(PHP_OS, 0, 3) == 'WIN') {
  die('skip Not for Windows');
}
require __DIR__ . '/../skipif_root.inc';
?>
--FILE--
<?php
/*
 * remove the execute permission from the parent dir and test dir() on child dir
 *   1) remove write & execute permission from the 1st parent and test dir()
 *   2) remove execute permission from 2nd parent and test dir()
 */

echo "*** Testing dir() : remove execute permission from the parent dir ***\n";

/* create the temporary directory :
  dir_variation7  ( parent )
    |-> sub_dir    ( sub parent )
         |-> child_dir  ( child dir)
*/
$parent_dir_path = TestPath::directory()->create(permissions: 0777);

// create sub_dir
$sub_dir_path = $parent_dir_path->directory()->create(permissions: 0777);

//create sub_sub_dir
$child_dir_path = $sub_dir_path->directory()->create();

// remove the write and execute permission from sub parent
chmod($sub_dir_path, 0444);
echo "-- After restricting 1st level parent directory --\n";
$d = dir($child_dir_path); // try to open, expected failure
var_dump( $d ); // dump it

// remove the execute permission from parent dir, allowing all permission for sub dir
chmod($sub_dir_path, 0777); // all permission to sub dir
chmod($parent_dir_path, 0666); // restricting parent directory
echo "-- After restricting parent directory --\n";
$d = dir($child_dir_path); // try to open, expected failure
var_dump( $d ); // dump it

echo "Done";

chmod($parent_dir_path, 0777);
chmod($sub_dir_path, 0777);
chmod($child_dir_path, 0777);
?>
--EXPECTF--
*** Testing dir() : remove execute permission from the parent dir ***
-- After restricting 1st level parent directory --

Warning: dir(): Failed to open directory: %s in %s on line %d
bool(false)
-- After restricting parent directory --

Warning: dir(): Failed to open directory: %s in %s on line %d
bool(false)
Done
