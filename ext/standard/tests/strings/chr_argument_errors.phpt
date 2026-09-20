--TEST--
Test chr() - argument errors
--FILE--
<?php

foreach ([
    fn() => chr(),
    fn() => chr(72, 10),
    fn() => chr([]),
] as $callback) {
    try {
        $callback();
    } catch (Throwable $e) {
        echo $e::class, ': ', $e->getMessage(), "\n";
    }
}

?>
--EXPECT--
ArgumentCountError: chr() expects exactly 1 argument, 0 given
ArgumentCountError: chr() expects exactly 1 argument, 2 given
TypeError: chr(): Argument #1 ($codepoint) must be of type int, array given
