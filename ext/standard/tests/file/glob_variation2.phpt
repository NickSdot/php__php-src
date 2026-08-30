--TEST--
Test glob() function with relative path
--FILE--
<?php
// temp dirname used here
$directory = TestPath::directory('glob_test');
$file = $directory->file('file.text');

// create temp directory
mkdir($directory);

// create temp file
$fp = fopen($file, "w");
fclose($fp);

echo "Testing glob() with relative paths:\n";

chdir($directory);
var_dump( glob("./*") );
var_dump( glob("../$directory->name/*"));

chdir($file->directory);
var_dump( glob("$directory->name/*"));
var_dump( glob("$directory->name"));

echo "Done\n";
?>
--EXPECT--
Testing glob() with relative paths:
array(1) {
  [0]=>
  string(11) "./file.text"
}
array(1) {
  [0]=>
  string(22) "../glob_test/file.text"
}
array(1) {
  [0]=>
  string(19) "glob_test/file.text"
}
array(1) {
  [0]=>
  string(9) "glob_test"
}
Done
