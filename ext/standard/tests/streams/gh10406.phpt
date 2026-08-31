--TEST--
GH-10406: feof() behavior change for UNIX based socket resources
--SKIPIF--
<?php
if (!strncasecmp(PHP_OS, 'WIN', 3)) die("skip. Do not run on Windows");
?>
--FILE--
<?php
$socket_path = Workspace::file();

$socket = stream_socket_server('unix://' . $socket_path);
var_dump(feof($socket));
?>
--EXPECT--
bool(false)
