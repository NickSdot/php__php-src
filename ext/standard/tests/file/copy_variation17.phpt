--TEST--
Test copy() function: usage variations - wildcard chars in source
--FILE--
<?php
/* Test copy(): Trying to copy the source file which is given with the combination of wild-card chars */

$file_path = Workspace::directory()->create();

echo "*** Test copy() function: With source file names containing wild-card chars ***\n";
$src_file = $file_path->file('copy_variation17.tmp');
$file_handle =  fopen($src_file, "w");
fwrite($file_handle, str_repeat("Hello2world...\n", 100));
fclose($file_handle);

$dir = $file_path->directory()->create();

$src_file_names = array(
  $src_file->path,  //without wild-card char
  $file_path."/copy*17.tmp",
  $file_path."/*_variation17.tmp",
  $file_path."/copy_variation*.tmp",
  $file_path."/*.tmp"
);

$count = 1;
foreach($src_file_names as $src_file_name) {
  $dest_file_name = $dir->file();
  var_dump( copy($src_file_name, $dest_file_name) );
  var_dump( file_exists($dest_file_name) );

  if( file_exists($dest_file_name) ) {
  var_dump( filesize($dest_file_name) );  //size of destination
  }

  $count++;
}

echo "*** Done ***\n";
?>
--EXPECTF--
*** Test copy() function: With source file names containing wild-card chars ***
bool(true)
bool(true)
int(1500)

Warning: copy(): %s
bool(false)
bool(false)

Warning: copy(): %s
bool(false)
bool(false)

Warning: copy(): %s
bool(false)
bool(false)

Warning: copy(): %s
bool(false)
bool(false)
*** Done ***
