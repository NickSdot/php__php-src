--TEST--
Bug #76839: socket_recvfrom may return an invalid 'from' address on MacOS
--EXTENSIONS--
sockets
--SKIPIF--
<?php
if (strtolower(substr(PHP_OS, 0, 3)) === 'win') {
    die('skip not valid for Windows.');
}
?>
--FILE--
<?php

// This bug only occurs when a specific portion of memory is unclean.
// Unfortunately, looping around 10 times and using random paths is the
// best way I could manage to reproduce this problem without modifying php itself :-/

for ($i = 0; $i < 10; $i++) {
    $senderSocketFile = TestPath::file(substr(md5(rand()), 0, rand(8, 16)) . '.sock');
    chdir($senderSocketFile->directory);
    $senderSocketPath = $senderSocketFile->name;
    $senderSocket = socket_create(AF_UNIX, SOCK_DGRAM, 0);
    socket_bind($senderSocket, $senderSocketPath);

    $receiverSocketFile = TestPath::file(substr(md5(rand()), 0, rand(8, 16)) . '.sock');
    $receiverSocketPath = $receiverSocketFile->name;
    $receiverSocket = socket_create(AF_UNIX, SOCK_DGRAM, 0);
    socket_bind($receiverSocket, $receiverSocketPath);

    // Send message from sender socket to receiver socket
    socket_sendto($senderSocket, 'Ping!', 5, 0, $receiverSocketPath);

    // Receive message on receiver socket
    $from = '';
    $message = '';
    socket_recvfrom($receiverSocket, $message, 65535, 0, $from);
    echo "Received '$message'\n";

    // Respond to the sender using the 'from' address from socket_recvfrom
    socket_sendto($receiverSocket, 'Pong!', 5, 0, $from);
    echo "Responded to sender with 'Pong!'\n";

    socket_close($receiverSocket);
    socket_close($senderSocket);
}
?>
--EXPECT--
Received 'Ping!'
Responded to sender with 'Pong!'
Received 'Ping!'
Responded to sender with 'Pong!'
Received 'Ping!'
Responded to sender with 'Pong!'
Received 'Ping!'
Responded to sender with 'Pong!'
Received 'Ping!'
Responded to sender with 'Pong!'
Received 'Ping!'
Responded to sender with 'Pong!'
Received 'Ping!'
Responded to sender with 'Pong!'
Received 'Ping!'
Responded to sender with 'Pong!'
Received 'Ping!'
Responded to sender with 'Pong!'
Received 'Ping!'
Responded to sender with 'Pong!'
