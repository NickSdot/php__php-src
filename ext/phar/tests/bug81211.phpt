--TEST--
Bug #81211 (Symlinks are followed when creating PHAR archive)
--EXTENSIONS--
phar
--SKIPIF--
<?php
if (PHP_OS_FAMILY === 'Windows') {
    if (false === include __DIR__ . '/../../standard/tests/file/windows_links/common.inc') {
        die('skip windows_links/common.inc is not available');
    }
    skipIfSeCreateSymbolicLinkPrivilegeIsDisabled(__FILE__);
}
?>
--FILE--
<?php
$root = TestPath::directory('bug81211');
$targetDir = $root->directory('foobar');
$fooDir = $root->directory('foo');
mkdir($root->path);
mkdir($targetDir->path);
mkdir($fooDir->path);

$targetFile = $targetDir->file('file');
$link = $fooDir->file('symlink');
file_put_contents($targetFile, 'this file should NOT be included in the archive!');
symlink($targetFile->path, $link->path);

$archiveFile = $root->file('archive.tar');
$archive = new PharData($archiveFile->path);
try {
    $archive->buildFromDirectory($fooDir->path);
} catch (UnexpectedValueException $ex) {
    echo $ex::class, ': ', $ex->getMessage(), PHP_EOL;
}
try {
    $archive->buildFromIterator(new RecursiveDirectoryIterator($fooDir->path, FilesystemIterator::SKIP_DOTS), $fooDir->path);
} catch (UnexpectedValueException $ex) {
    echo $ex::class, ': ', $ex->getMessage(), PHP_EOL;
}
?>
--EXPECTF--
UnexpectedValueException: Iterator RecursiveIteratorIterator returned a path "%s%ebug81211%efoobar%efile" that is not in the base directory "%s%ebug81211%efoo"
UnexpectedValueException: Iterator RecursiveDirectoryIterator returned a path "%s%ebug81211%efoobar%efile" that is not in the base directory "%s%ebug81211%efoo"
