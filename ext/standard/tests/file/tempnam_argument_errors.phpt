--TEST--
Test tempnam() - argument errors
--FILE--
<?php

foreach ([
    fn() => tempnam([], 'prefix'),
    fn() => tempnam(sys_get_temp_dir(), []),
] as $callback) {
    try {
        $callback();
    } catch (Throwable $e) {
        echo $e::class, ': ', $e->getMessage(), "\n";
    }
}

?>
--EXPECT--
TypeError: tempnam(): Argument #1 ($directory) must be of type string, array given
TypeError: tempnam(): Argument #2 ($prefix) must be of type string, array given
