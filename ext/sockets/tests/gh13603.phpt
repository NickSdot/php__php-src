--TEST--
GH-13603 -  socket_getsockname - invalid characters
--EXTENSIONS--
sockets
--FILE--
<?php
$path = Workspace::file('sn.socp');
chdir($path->directory);
$address = $path->name;
        $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
        socket_bind($socket, $address);
        socket_listen($socket);
        socket_getsockname($socket, $actual_address);
        var_dump($actual_address);
        socket_close($socket);
--EXPECT--
string(7) "sn.socp"
