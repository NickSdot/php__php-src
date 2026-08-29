--TEST--
SPL: DirectoryIterator::getExtension() basic test
--FILE--
<?php
TestContext::useRelativeWorkspace();
$dir = TestPath::directory(md5('DirectoryIterator::getExtension'));

if (!mkdir($dir->path)) {
    die('Failed to create test directory');
}

$files = array('test.txt', 'test.extension', 'test');
foreach ($files as $file) {
    touch($dir->file($file)->path);
}

$dit_exts = array();
$nfo_exts = array();

foreach (new DirectoryIterator($dir) as $file) {
    if ($file->isDot()) {
        continue;
    }

    $dit_exts[] = $file->getExtension();
    $nfo_exts[] = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
}
var_dump($dit_exts === $nfo_exts);
sort($dit_exts);
var_dump($dit_exts);
?>
--EXPECT--
bool(true)
array(3) {
  [0]=>
  string(0) ""
  [1]=>
  string(9) "extension"
  [2]=>
  string(3) "txt"
}
