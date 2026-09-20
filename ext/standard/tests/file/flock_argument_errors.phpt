--TEST--
Test flock() - argument errors
--FILE--
<?php

$stream = fopen('php://memory', 'r+');

try {
    flock($stream, []);
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
TypeError: flock(): Argument #2 ($operation) must be of type int, array given
