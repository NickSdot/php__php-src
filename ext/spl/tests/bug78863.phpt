--TEST--
Bug #78863 (DirectoryIterator class silently truncates after a null byte)
--FILE--
<?php
$dir = Workspace::directory()->create();
$dir->file('bad')->create();
$dir->directory('sub')->create()->file('good')->create();

$it = new DirectoryIterator($dir . "\0/sub");
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
