--TEST--
File::create() uses the umask-derived default file permissions
--SKIPIF--
<?php

if (PHP_OS_FAMILY === 'Windows') {
    die('skip Unix-style file permissions are not available on Windows');
}

?>
--FILE--
<?php

$previousUmask = umask(0022);
$file = TestPath::file('default-permissions')->create();

clearstatcache();

var_dump((fileperms($file->path) & 0777) === 0644);

umask($previousUmask);

?>
--EXPECT--
bool(true)
