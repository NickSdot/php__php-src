--TEST--
Testing ftp_nb_fget resume parameter
--CREDITS--
Rodrigo Moyle <eu [at] rodrigorm [dot] com [dot] br>
#testfest PHPSP on 2009-06-20
--EXTENSIONS--
ftp
pcntl
--FILE--
<?php
require 'server.inc';

$ftp = ftp_connect('127.0.0.1', $port);
ftp_login($ftp, 'user', 'pass');
if (!$ftp) die("Couldn't connect to the server");

$local_file = Workspace::file();
file_put_contents($local_file, 'ASCIIFoo');
$handle = fopen($local_file, 'a');

var_dump(ftp_nb_fget($ftp, $handle, 'fgetresume.txt', FTP_ASCII, 8));
var_dump(file_get_contents($local_file));
?>
--EXPECT--
int(2)
string(12) "ASCIIFooBar
"
