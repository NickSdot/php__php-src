--TEST--
Test stristr() - argument errors
--FILE--
<?php

try {
    stristr('abc', []);
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
TypeError: stristr(): Argument #2 ($needle) must be of type string, array given
