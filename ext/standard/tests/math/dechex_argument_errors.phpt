--TEST--
Test dechex() - argument errors
--FILE--
<?php

try {
    dechex([]);
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
TypeError: dechex(): Argument #1 ($num) must be of type int, array given
