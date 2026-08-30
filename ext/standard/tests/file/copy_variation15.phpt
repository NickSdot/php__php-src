--TEST--
Test copy() function: usage variations - destination dir access perms
--SKIPIF--
<?php
if(substr(PHP_OS, 0, 3) == 'WIN')
  die("skip do not run on Windows");
require __DIR__ . '/../skipif_root.inc';
?>
--FILE--
<?php
/* Test copy(): Trying to create a copy of file in a dir which doesn't have write permissions */

echo "*** Test copy() function: Trying to create a copy of file in a dir which doesn't have write permissions ***";
$file = TestPath::file();
$file_handle =  fopen($file, "w");
fwrite($file_handle, str_repeat("Hello, world...", 20));
fclose($file_handle);

$dir = TestPath::directory()->create();

$old_perms = fileperms($dir);

chmod($dir, 0555);  //dir without write permissions

$dest = $dir."/copy_copy_variation15.tmp";

var_dump( copy($file, $dir."/copy_copy_variation15.tmp") );
var_dump( file_exists($dir."/copy_copy_variation15_dir.tmp") );
var_dump( filesize($file) );  //size of source

chmod($dir, $old_perms);

echo "*** Done ***\n";
?>
--EXPECTF--
*** Test copy() function: Trying to create a copy of file in a dir which doesn't have write permissions ***
Warning: copy(): %s
bool(false)
bool(false)
int(300)
*** Done ***
