--TEST--
Test fprintf() - argument errors
--FILE--
<?php

$stream = fopen('php://memory', 'r+');

foreach ([
    fn() => fprintf(),
    fn() => fprintf($stream),
    fn() => fprintf($stream, []),
] as $callback) {
    try {
        $callback();
    } catch (Throwable $e) {
        echo $e::class, ': ', $e->getMessage(), "\n";
    }
}

?>
--EXPECT--
ArgumentCountError: fprintf() expects at least 2 arguments, 0 given
ArgumentCountError: fprintf() expects at least 2 arguments, 1 given
TypeError: fprintf(): Argument #2 ($format) must be of type string, array given
