--TEST--
BcMath\Number calc undefined var by operator
--EXTENSIONS--
bcmath
--FILE--
<?php
$num = new BcMath\Number(100);

try {
    $num + null;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $num - null;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $num * null;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $num / null;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $num % null;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $num ** null;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECT--
TypeError: Unsupported operand types: BcMath\Number + null
TypeError: Unsupported operand types: BcMath\Number - null
TypeError: Unsupported operand types: BcMath\Number * null
TypeError: Unsupported operand types: BcMath\Number / null
TypeError: Unsupported operand types: BcMath\Number % null
TypeError: Unsupported operand types: BcMath\Number ** null
