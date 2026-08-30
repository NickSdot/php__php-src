--TEST--
Bug #74357 (lchown fails to change ownership of symlink with ZTS)
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) == 'WIN') die('skip no windows support');
if (!function_exists("posix_getpwuid")) die('skip no posix_getpwuid()');
require __DIR__ . '/../skipif_no_root.inc';
if (posix_getpwuid(1000) === false) die('skip no user with uid 1000')
?>
--FILE--
<?php
$link = TestPath::file()->path;
$dir = TestPath::directory();
mkdir($dir);
symlink($dir->path, $link);
lchown($link, 1000);
var_dump(lstat($link)['uid']);

?>
--EXPECT--
int(1000)
