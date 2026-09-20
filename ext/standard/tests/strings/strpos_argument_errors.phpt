--TEST--
Test strpos() - argument errors
--FILE--
<?php

try {
    strpos('abc', 'a', []);
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
TypeError: strpos(): Argument #3 ($offset) must be of type int, array given
