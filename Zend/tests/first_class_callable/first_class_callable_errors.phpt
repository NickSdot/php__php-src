--TEST--
Trying to acquire callable to something that's not callable
--FILE--
<?php

class Test {
    private static function privateMethod() {}

    public function instanceMethod() {}
}

try {
    $fn = 123;
    $fn(...);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    does_not_exist(...);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    stdClass::doesNotExist(...);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    (new stdClass)->doesNotExist(...);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    [new stdClass, 'doesNotExist'](...);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    Test::privateMethod(...);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    Test::instanceMethod(...);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Value of type int is not callable
Error: Call to undefined function does_not_exist()
Error: Call to undefined method stdClass::doesNotExist()
Error: Call to undefined method stdClass::doesNotExist()
Error: Call to undefined method stdClass::doesNotExist()
Error: Call to private method Test::privateMethod() from global scope
Error: Non-static method Test::instanceMethod() cannot be called statically
