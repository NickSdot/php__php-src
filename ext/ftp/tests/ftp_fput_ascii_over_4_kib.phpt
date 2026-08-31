--TEST--
Testing ftp_fput basic functionality
--EXTENSIONS--
ftp
pcntl
--FILE--
<?php
require 'server.inc';

// server.inc reads relative to its directory.
Workspace::useRelative();

$ftp = ftp_connect('127.0.0.1', $port);
ftp_login($ftp, 'user', 'pass');
$ftp or die("Couldn't connect to the server");

$filepath = Workspace::file('large_file.txt');
$filename = basename($filepath->directory) . '/' . $filepath->name;

// Test on boundary of 2 buffers
for ($i = 4094; $i < 4098; $i++) {
    $contents = str_repeat("a", $i) . "\n" . str_repeat("b", 10);
    file_put_contents($filepath, $contents);
    var_dump(ftp_put($ftp, $filename, $filepath, FTP_ASCII));
}

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
