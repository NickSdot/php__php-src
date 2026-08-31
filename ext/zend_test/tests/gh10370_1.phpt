--TEST--
GH-10370: File corruption in _php_stream_copy_to_stream_ex when using copy_file_range - partial copy
--EXTENSIONS--
zend_test
phar
--SKIPIF--
<?php
if (PHP_OS != 'Linux') {
    die('skip For Linux only');
}
?>
--INI--
zend_test.limit_copy_file_range=3584
--FILE--
<?php
/* Note: the value 3584 is chosen so that the mmap in _php_stream_copy_to_stream_ex() will mmap
 *       at an offset of a multiple of 4096, which is the standard page size in most Linux systems. */
$archive = new PharData(__DIR__ . DIRECTORY_SEPARATOR . 'gh10370.tar');
$directory = Workspace::directory();
$file = $directory->file('testfile');
var_dump($archive->extractTo($directory, ['testfile']));
var_dump(sha1_file($file));
?>
--EXPECT--
bool(true)
string(40) "a723ae4ec7eababff73ca961a771b794be6388d2"
