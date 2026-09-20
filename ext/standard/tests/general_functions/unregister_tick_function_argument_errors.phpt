--TEST--
Test unregister_tick_function() - argument errors
--FILE--
<?php

try {
    unregister_tick_function('missing_function');
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
TypeError: unregister_tick_function(): Argument #1 ($callback) must be a valid callback, function "missing_function" not found or invalid function name
