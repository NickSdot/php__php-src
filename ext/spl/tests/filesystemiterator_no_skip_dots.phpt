--TEST--
FileSystemIterator without SKIP_DOTS
--FILE--
<?php

TestContext::useRelativeWorkspace();
$dir = TestPath::directory('filesystemiterator_no_skip_dots');
mkdir($dir->path);
$file = $dir->file('file');
touch($file->path);

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
