--TEST--
BcMath\Number calc array by operator
--EXTENSIONS--
bcmath
--FILE--
<?php
$num = new BcMath\Number(100);
$array = [1];

try {
    $num + $array;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $num - $array;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $num * $array;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $num / $array;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $num % $array;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $num ** $array;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECT--
TypeError: Unsupported operand types: BcMath\Number + array
TypeError: Unsupported operand types: BcMath\Number - array
TypeError: Unsupported operand types: BcMath\Number * array
TypeError: Unsupported operand types: BcMath\Number / array
TypeError: Unsupported operand types: BcMath\Number % array
TypeError: Unsupported operand types: BcMath\Number ** array
