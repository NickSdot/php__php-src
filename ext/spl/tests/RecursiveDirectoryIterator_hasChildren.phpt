--TEST--
SPL: RecursiveDirectoryIterator::hasChildren() follow symlinks test
--FILE--
<?php

TestContext::useRelativeWorkspace();
$dir = TestPath::directory('symlinktest');

if (!mkdir($dir->path)) {
    die('Failed to create temporary directory for testing');
} elseif (!symlink(__DIR__, $dir->file('symlink')->path)) {
    die('Failed to create symbolic link');
}

$it = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS | FilesystemIterator::KEY_AS_FILENAME);

var_dump($it->key());
var_dump($it->hasChildren());

$it->setFlags(FilesystemIterator::SKIP_DOTS | FilesystemIterator::KEY_AS_FILENAME);

var_dump($it->key());
var_dump($it->hasChildren());

?>
--EXPECT--
string(7) "symlink"
bool(true)
string(7) "symlink"
bool(false)
