--TEST--
Test is_file() function: usage variations - diff. path notations (Bug #42027)
--FILE--
<?php
/* Passing file names with different notations, using slashes, wild-card chars */

echo "*** Testing is_file() with different notations of file names ***\n";
$directory = TestPath::directory('is_file_variation4');
$file = $directory->file('is_file_variation4.tmp');
mkdir($directory);
$file_path = dirname($directory->path);
$dir_name = $directory;
mkdir($dir_name);
$file_handle = fopen($file, "w");
fclose($file_handle);

$files_arr = array(
  "/$directory->name/$file->name",

  /* Testing a file trailing slash */
  "/$directory->name/$file->name/",

  /* Testing file with double slashes */
  "/$directory->name//$file->name",
  "//$directory->name//$file->name",
  "/$directory->name/*.tmp",
  "$directory->name/is_file*.tmp",
);

$count = 1;
/* loop through to test each element in the above array */
foreach($files_arr as $file) {
  echo "- Iteration $count -\n";
  try {
    var_dump( is_file( $file_path."/".$file ) );
  } catch (Error $e) {
    echo $e->getMessage(), "\n";
  }
  clearstatcache();
  $count++;
}

echo "\n*** Done ***";
?>
--EXPECT--
*** Testing is_file() with different notations of file names ***
- Iteration 1 -
bool(true)
- Iteration 2 -
bool(false)
- Iteration 3 -
bool(true)
- Iteration 4 -
bool(true)
- Iteration 5 -
bool(false)
- Iteration 6 -
bool(false)

*** Done ***
