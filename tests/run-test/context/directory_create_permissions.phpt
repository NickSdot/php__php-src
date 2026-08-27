--TEST--
Directory::create() applies explicitly requested directory permissions
--SKIPIF--
<?php

if (PHP_OS_FAMILY === 'Windows') {
    die('skip Unix-style directory permissions are not available on Windows');
}

?>
--FILE--
<?php

$directory = TestPath::directory()->create(permissions: 0700);

clearstatcache();

var_dump((fileperms($directory) & 0777) === 0700);

?>
--EXPECT--
bool(true)
