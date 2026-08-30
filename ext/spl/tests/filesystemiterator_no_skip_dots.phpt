--TEST--
FileSystemIterator without SKIP_DOTS
--FILE--
<?php

$dir = TestPath::directory()->create();
$dir->file('file')->create();

$it = new FileSystemIterator($dir, 0);
$files = [];
foreach ($it as $f) {
    $files[] = $f->getFileName();
}
sort($files);
var_dump($files);

?>
--EXPECT--
array(3) {
  [0]=>
  string(1) "."
  [1]=>
  string(2) ".."
  [2]=>
  string(4) "file"
}
