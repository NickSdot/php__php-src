--TEST--
Test str_replace() - argument errors
--FILE--
<?php

$count = null;

foreach ([
    function () {
        $count = null;
        str_replace(new stdClass(), 'b', 'abc', $count);
    },
    function () {
        $count = null;
        str_replace('a', new stdClass(), 'abc', $count);
    },
    function () {
        $count = null;
        str_replace('a', 'b', new stdClass(), $count);
    },
    fn() => str_replace('a', 'b'),
    fn() => str_replace('a', 'b', 'abc', $count, 'extra'),
] as $callback) {
    try {
        $callback();
    } catch (Throwable $e) {
        echo $e::class, ': ', $e->getMessage(), "\n";
    }
}

?>
--EXPECT--
TypeError: str_replace(): Argument #1 ($search) must be of type array|string, stdClass given
TypeError: str_replace(): Argument #2 ($replace) must be of type array|string, stdClass given
TypeError: str_replace(): Argument #3 ($subject) must be of type array|string, stdClass given
ArgumentCountError: str_replace() expects at least 3 arguments, 2 given
ArgumentCountError: str_replace() expects at most 4 arguments, 5 given
