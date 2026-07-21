--TEST--
bcfloor() function with error
--EXTENSIONS--
bcmath
--FILE--
<?php
try {
    bcfloor('hoge');
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    bcfloor('0.00.1');
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECT--
ValueError: bcfloor(): Argument #1 ($num) is not well-formed
ValueError: bcfloor(): Argument #1 ($num) is not well-formed
