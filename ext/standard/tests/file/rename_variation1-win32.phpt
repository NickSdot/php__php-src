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

/* creating directory */
$file_path = TestPath::directory();
$source_dir = $file_path->directory();
$dest_dir = $file_path->directory();
$new_dir = $dest_dir->directory();

// rename dirs across directories
echo "\n*** Testing rename() : renaming directory across directories ***\n";
$src_dirs = array (
  /* Testing simple directory tree */
  "$file_path/{$source_dir->name}/",

  /* Testing a dir with trailing slash */
  "$file_path/{$source_dir->name}/",

  /* Testing dir with double trailing slashes */
  "$file_path//{$source_dir->name}//",
);

// create the $dest_dir
mkdir($file_path);
mkdir($dest_dir);

$counter = 1;

/* loop through each $src_dirs and rename it to  $dest_dir */
foreach($src_dirs as $src_dir) {
  echo "-- Iteration $counter --\n";

  // create the src dir
  mkdir($source_dir);
  // rename the src dir to a new dir in dest dir
  var_dump( rename($src_dir, $new_dir) );
  // ensure that dir was renamed
  var_dump( file_exists($src_dir) );  // expecting false
  var_dump( file_exists($new_dir) ); // expecting true

  // remove the new dir
  rmdir($new_dir);
  $counter++;
}

echo "Done\n";
?>
--EXPECT--
*** Testing rename() : renaming directory across directories ***
-- Iteration 1 --
bool(true)
bool(false)
bool(true)
-- Iteration 2 --
bool(true)
bool(false)
bool(true)
-- Iteration 3 --
bool(true)
bool(false)
bool(true)
Done
