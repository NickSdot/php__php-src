--TEST--
Bug #78810: RW fetches do not throw "uninitialized property" exception
--FILE--
<?php

class Test {
    public int $i;
}

$test = new Test;
try {
    $test->i++;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $test->i += 1;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Typed property Test::$i must not be accessed before initialization
Error: Typed property Test::$i must not be accessed before initialization
