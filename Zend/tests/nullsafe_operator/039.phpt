--TEST--
Handling of undef variable exception in JMP_NULL
--FILE--
<?php

set_error_handler(function($_, $m) {
    throw new Exception($m);
});

try {
    $foo?->foo;
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Exception: Undefined variable $foo
