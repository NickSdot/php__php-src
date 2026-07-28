<?php

$socketPath = tempnam(sys_get_temp_dir(), 'php_socket_');
if ($socketPath === false) {
    die('skip unable to create a temporary socket path');
}
unlink($socketPath);

$socket = socket_create(AF_UNIX, SOCK_DGRAM, 0);
if ($socket === false || !@socket_bind($socket, $socketPath)) {
    if ($socket !== false) {
        socket_close($socket);
    }
    die('skip unable to bind an AF_UNIX datagram socket');
}
socket_close($socket);
unlink($socketPath);
