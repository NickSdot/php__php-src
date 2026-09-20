--TEST--
Test strtr() - argument errors
--FILE--
<?php

foreach ([
    fn() => strtr('abc', 'a'),
    fn() => strtr('abc', [], 'x'),
] as $callback) {
    try {
        $callback();
    } catch (Throwable $e) {
        echo $e::class, ': ', $e->getMessage(), "\n";
    }
}

?>
--EXPECT--
TypeError: strtr(): Argument #2 ($from) must be of type array, string given
TypeError: strtr(): Argument #2 ($from) must be of type string, array given
