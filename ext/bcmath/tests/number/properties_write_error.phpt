--TEST--
BcMath\Number properties write error
--EXTENSIONS--
bcmath
--FILE--
<?php

$num = new BcMath\Number(1);
try {
    $num->value = 1;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $num->scale = 1;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECT--
Error: Cannot modify readonly property BcMath\Number::$value
Error: Cannot modify readonly property BcMath\Number::$scale
