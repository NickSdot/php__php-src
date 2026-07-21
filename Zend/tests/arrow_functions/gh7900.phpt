--TEST--
GH-7900: Arrow function with never return type compile-time errors
--INI--
zend.assertions=1
assert.exception=1
--FILE--
<?php

$x = fn(): never => throw new \Exception('Here');

try {
    var_dump($x());
} catch (\Exception $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    assert((fn(): never => 42) && false);
} catch (\Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Exception: Here
AssertionError: assert((fn(): never => 42) && false)
