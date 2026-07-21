--TEST--
Bug #44827 (define() allows :: in constant names)
--FILE--
<?php

try {
    define('foo::bar', 1);
} catch (ValueError $exception) {
    echo $exception::class, ': ', $exception->getMessage(), PHP_EOL;
}

try {
    define('::', 1);
} catch (ValueError $exception) {
    echo $exception::class, ': ', $exception->getMessage(), PHP_EOL;
}

?>
--EXPECT--
ValueError: define(): Argument #1 ($constant_name) cannot be a class constant
ValueError: define(): Argument #1 ($constant_name) cannot be a class constant
