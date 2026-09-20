--TEST--
Test crypt() - argument errors
--FILE--
<?php

foreach ([
    fn() => crypt('string'),
    fn() => crypt('string', 'salt', 'extra'),
    fn() => crypt([], 'salt'),
    fn() => crypt('string', []),
] as $callback) {
    try {
        $callback();
    } catch (Throwable $e) {
        echo $e::class, ': ', $e->getMessage(), "\n";
    }
}

?>
--EXPECT--
ArgumentCountError: crypt() expects exactly 2 arguments, 1 given
ArgumentCountError: crypt() expects exactly 2 arguments, 3 given
TypeError: crypt(): Argument #1 ($string) must be of type string, array given
TypeError: crypt(): Argument #2 ($salt) must be of type string, array given
