--TEST--
Test is_readable() function: usage variations - diff. file notations
--SKIPIF--
<?php
require __DIR__ . '/../skipif_root.inc';
?>
--FILE--
<?php
/* test is_readable() with file having different filepath notation */

require __DIR__.'/file.inc';
echo "*** Testing is_readable(): usage variations ***\n";

$directory = TestPath::directory();
mkdir($directory);
$file = $directory->file('bar.tmp');
$filename = $file->name;

// create a new temporary file
$fp = fopen($file, "w");
fclose($fp);

/* array of files to be tested if they are readable by using
   is_readable() function */
$files_arr = array(
  "$directory/$filename",

  /* Testing a file trailing slash */
  "$directory/$filename/",

  /* Testing file with double slashes */
  "$directory//$filename",
  "$directory/../$directory->name//$filename",
  "$directory/*.tmp",
  "$directory/b*.tmp",

  /* Testing directories */
  ".",  // current directory, exp: bool(true)
  "$directory"  // temp directory, exp: bool(true)
);
$counter = 1;
/* loop through to test each element in the above array
   is a writable file */
foreach($files_arr as $file) {
  echo "-- Iteration $counter --\n";
  try {
    var_dump( is_readable($file) );
  } catch (Error $e) {
    echo $e->getMessage(), "\n";
  }
  $counter++;
  clearstatcache();
}

echo "Done\n";
?>
--EXPECTF--
*** Testing is_readable(): usage variations ***
-- Iteration 1 --
bool(true)
-- Iteration 2 --
bool(%s)
-- Iteration 3 --
bool(true)
-- Iteration 4 --
bool(true)
-- Iteration 5 --
bool(false)
-- Iteration 6 --
bool(false)
-- Iteration 7 --
bool(true)
-- Iteration 8 --
bool(true)
Done
