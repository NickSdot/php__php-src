--TEST--
Test bindec() - argument errors
--FILE--
<?php

try {
    bindec([]);
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
TypeError: bindec(): Argument #1 ($binary_string) must be of type string, array given
