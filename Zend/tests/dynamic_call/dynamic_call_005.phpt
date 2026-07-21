--TEST--
Dynamic calls to scope introspection functions are forbidden
--FILE--
<?php

function test_calls($func) {
    $i = 1;

    try {
        array_map($func, [['i' => new stdClass]]);
        var_dump($i);
    } catch (\Error $e) {
        echo $e::class, ': ', $e->getMessage(), PHP_EOL;
    }

    try {
        $func(['i' => new stdClass]);
        var_dump($i);
    } catch (\Error $e) {
        echo $e::class, ': ', $e->getMessage(), PHP_EOL;
    }

    try {
        call_user_func($func, ['i' => new stdClass]);
        var_dump($i);
    } catch (\Error $e) {
        echo $e::class, ': ', $e->getMessage(), PHP_EOL;
    }
}
test_calls('extract');

?>
--EXPECT--
Error: Cannot call extract() dynamically
Error: Cannot call extract() dynamically
Error: Cannot call extract() dynamically
