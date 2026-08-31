--TEST--
Test is_writable() and its alias is_writeable() function: usage variations - diff. path notations
--SKIPIF--
<?php
require __DIR__ . '/../skipif_root.inc';
?>
--FILE--
<?php
/* test is_writable() & is_writeable() with file having different filepath notation */
require __DIR__.'/file.inc';
echo "*** Testing is_writable(): usage variations ***\n";

$directory = Workspace::directory()->create();
$file = $directory->file('bar.tmp');
$filename = $file->name;

// create a new temporary file
$fp = fopen($file, "w");
fclose($fp);

/* array of files to be tested to check if they are writable
   using is_writable() function */
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
    var_dump( is_writable($file) );
  } catch (Error $e) {
    echo $e->getMessage(), "\n";
  }
  try {
    var_dump( is_writeable($file) );
  } catch (Error $e) {
    echo $e->getMessage(), "\n";
  }
  $counter++;
  clearstatcache();
}

echo "Done\n";
?>
--EXPECTF--
*** Testing is_writable(): usage variations ***
-- Iteration 1 --
bool(true)
bool(true)
-- Iteration 2 --
bool(%s)
bool(%s)
-- Iteration 3 --
bool(true)
bool(true)
-- Iteration 4 --
bool(true)
bool(true)
-- Iteration 5 --
bool(false)
bool(false)
-- Iteration 6 --
bool(false)
bool(false)
-- Iteration 7 --
bool(true)
bool(true)
-- Iteration 8 --
bool(true)
bool(true)
Done
