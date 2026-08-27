--TEST--
File::create() applies explicitly requested file permissions
--SKIPIF--
<?php

if (PHP_OS_FAMILY === 'Windows') {
    die('skip Unix-style file permissions are not available on Windows');
}

?>
--FILE--
<?php

$file = TestPath::file('custom-permissions')->create(permissions: 0600);

clearstatcache();

var_dump((fileperms($file->path) & 0777) === 0600);

?>
--EXPECT--
bool(true)
