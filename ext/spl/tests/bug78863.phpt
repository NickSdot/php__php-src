--TEST--
Bug #78863 (DirectoryIterator class silently truncates after a null byte)
--FILE--
<?php
TestContext::useRelativeWorkspace();
$dir = TestPath::directory('bug78863');
mkdir($dir->path);
touch($dir->file('bad')->path);
$sub = $dir->directory('sub');
mkdir($sub->path);
touch($sub->file('good')->path);

$it = new DirectoryIterator($dir->path . "\0/sub");
foreach ($it as $fileinfo) {
    if (!$fileinfo->isDot()) {
        var_dump($fileinfo->getFilename());
    }
}
?>
--EXPECTF--
Fatal error: Uncaught ValueError: DirectoryIterator::__construct(): Argument #1 ($directory) must not contain any null bytes in %s:%d
Stack trace:
#0 %s(%d): DirectoryIterator->__construct('%s')
#1 {main}
  thrown in %s on line %d
