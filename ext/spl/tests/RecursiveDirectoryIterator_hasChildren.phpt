--TEST--
SPL: RecursiveDirectoryIterator::hasChildren() follow symlinks test
--FILE--
<?php

$dir = Workspace::directory()->create();

if (!symlink(__DIR__, $dir->file('symlink'))) {
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
