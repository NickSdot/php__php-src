--TEST--
Check permissions for created errorlog file
--SKIPIF--
<?php
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    die("skip this test on windows");
}
?>
--INI--
error_log=error_permissions_test.log
error_log_mode=0600
--FILE--
<?php

$logFile = Workspace::file();
ini_set('error_log', $logFile->path);

$oldMask = umask(0000);

try {
    error_log("hello world");

    assert(file_exists($logFile));

    printf("got permissions=%o\n", fileperms($logFile) & 0777);
    printf("errorlog contents\n%s", file_get_contents($logFile));
} finally {
    umask($oldMask);
}
?>
--EXPECTF--
got permissions=600
errorlog contents
[%d-%s-%d %d:%d:%d %s] hello world
