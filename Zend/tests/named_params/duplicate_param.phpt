--TEST--
Duplicate param
--FILE--
<?php

function test($a) {}

try {
    test(a: 1, a: 2);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    test(1, a: 2);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Named parameter $a overwrites previous argument
Error: Named parameter $a overwrites previous argument
