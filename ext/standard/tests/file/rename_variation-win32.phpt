--TEST--
Test rename() function: usage variations
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) != 'WIN') {
    die('skip.. only for Windows');
}
?>
--FILE--
<?php
require __DIR__.'/file.inc';

/* create directory */
$file_path = TestPath::directory();
$root = $file_path->directory();
$source = $root->file();
$dest = $file_path->file();
mkdir($file_path);
mkdir($root);

/* rename files across directories */
echo "*** Testing rename() : rename files across directories ***\n";
$src_filenames = array(
  "$file_path/{$root->name}/{$source->name}",

  /* Testing a file trailing slash */
  "$file_path/{$root->name}/{$source->name}/",

  /* Testing file with double slashes */
  "$file_path/{$root->name}//{$source->name}",
  "$file_path//{$root->name}//{$source->name}",
);

$counter = 1;

/* loop through each $file and rename it to rename_variation2.tmp */
foreach($src_filenames as $src_filename) {
  echo "-- Iteration $counter --\n";
  $fp = fopen($source, "w");
  fclose($fp);
  var_dump( rename($src_filename, $dest) );

  // ensure that file got renamed to new name
  var_dump( file_exists($src_filename) );  // expecting false
  var_dump( file_exists($dest) );  // expecting true
  $counter++;

  // unlink the file
  unlink($dest);
}

echo "Done\n";
?>
--EXPECTF--
*** Testing rename() : rename files across directories ***
-- Iteration 1 --
bool(true)
bool(false)
bool(true)
-- Iteration 2 --

Warning: rename(): %rThe filename, directory name, or volume label syntax is incorrect|The directory name is invalid%r (code: %r123|267%r) in %s on line %d
bool(false)
bool(false)
bool(false)

Warning: unlink(): No such file or directory in %s on line %d
-- Iteration 3 --
bool(true)
bool(false)
bool(true)
-- Iteration 4 --
bool(true)
bool(false)
bool(true)
Done
