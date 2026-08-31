--TEST--
Test glob() function: basic functions
--FILE--
<?php
echo "*** Testing glob() : basic functions ***\n";

// temp dirname used here
$dirname = Workspace::directory('glob_basic')->create();

// temp files created
$dirname->file('wonder12345')->create();
$dirname->file('wonder.txt')->create();
$dirname->file('file.text')->create();

// glob() with default arguments
sort_var_dump( glob($dirname."/*") );
sort_var_dump( glob($dirname."/*.txt") );
sort_var_dump( glob($dirname."/*.t?t") );
sort_var_dump( glob($dirname."/*.t*t") );
sort_var_dump( glob($dirname."/*.?") );
sort_var_dump( glob($dirname."/*.*") );

echo "Done\n";

function sort_var_dump($results) {
   sort($results);
   var_dump($results);
}
?>
--EXPECTF--
*** Testing glob() : basic functions ***
array(3) {
  [0]=>
  string(%d) "%s/glob_basic/file.text"
  [1]=>
  string(%d) "%s/glob_basic/wonder.txt"
  [2]=>
  string(%d) "%s/glob_basic/wonder12345"
}
array(1) {
  [0]=>
  string(%d) "%s/glob_basic/wonder.txt"
}
array(1) {
  [0]=>
  string(%d) "%s/glob_basic/wonder.txt"
}
array(2) {
  [0]=>
  string(%d) "%s/glob_basic/file.text"
  [1]=>
  string(%d) "%s/glob_basic/wonder.txt"
}
array(0) {
}
array(2) {
  [0]=>
  string(%d) "%s/glob_basic/file.text"
  [1]=>
  string(%d) "%s/glob_basic/wonder.txt"
}
Done
