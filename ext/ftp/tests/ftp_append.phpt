--TEST--
ftp_append() create new file and append something
--EXTENSIONS--
ftp
pcntl
--FILE--
<?php
require 'server.inc';

// server.inc appends relative to its directory.
Workspace::useRelative();

$fooBarPath = Workspace::file();
$remotePath = basename($fooBarPath->directory) . '/' . $fooBarPath->name;

$ftp = ftp_connect('127.0.0.1', $port);
if (!$ftp) die("Couldn't connect to the server");

var_dump(ftp_login($ftp, 'user', 'pass'));

$fooPath = Workspace::file();
file_put_contents($fooPath, 'foo');
var_dump(ftp_append($ftp, $remotePath, $fooPath, FTP_BINARY));

$barPath = Workspace::file();
file_put_contents($barPath, 'bar');
var_dump(ftp_append($ftp, $remotePath, $barPath, FTP_BINARY));

var_dump(file_get_contents($fooBarPath));

ftp_close($ftp);
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
string(6) "foobar"
