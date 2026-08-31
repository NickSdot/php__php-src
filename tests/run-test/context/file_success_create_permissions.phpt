--TEST--
Test File::create() - default permissions respect umask and explicit permissions override it
--SKIPIF--
<?php

if (PHP_OS_FAMILY === 'Windows') {
    die('skip Unix-style file permissions are not available on Windows');
}

?>
--FILE--
<?php

$previousUmask = umask(0077);

$default = Workspace::file()->create();
$explicit = Workspace::file()->create(permissions: 0644);

printf("default: %04o\n", fileperms($default) & 0777);
printf("explicit: %04o\n", fileperms($explicit) & 0777);

umask($previousUmask);

?>
--EXPECT--
default: 0600
explicit: 0644
