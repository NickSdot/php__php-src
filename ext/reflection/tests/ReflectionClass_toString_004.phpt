--TEST--
Constant evaluation exception during ReflectionClass::__toString()
--FILE--
<?php

class A {
    const C = self::UNKNOWN;
}
try {
    echo new ReflectionClass(A::class);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Undefined constant self::UNKNOWN
