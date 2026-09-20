--TEST--
Test decbin() - argument errors
--FILE--
<?php

try {
    decbin([]);
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
TypeError: decbin(): Argument #1 ($num) must be of type int, array given
