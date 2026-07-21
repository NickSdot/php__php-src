--TEST--
BcMath\Number construct error
--EXTENSIONS--
bcmath
--FILE--
<?php
try {
    $num = new BcMath\Number('1');
    $num->__construct(5);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $num = new BcMath\Number('a');
    var_dump($num);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECT--
Error: Cannot modify readonly property BcMath\Number::$value
ValueError: BcMath\Number::__construct(): Argument #1 ($num) is not well-formed
