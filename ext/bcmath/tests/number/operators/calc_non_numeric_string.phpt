--TEST--
BcMath\Number calc non-numeric string by operator
--EXTENSIONS--
bcmath
--FILE--
<?php
$num = new BcMath\Number(100);

try {
    $num + 'a';
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $num - 'a';
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $num * 'a';
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $num / 'a';
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $num % 'a';
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $num ** 'a';
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECT--
ValueError: Right string operand cannot be converted to BcMath\Number
ValueError: Right string operand cannot be converted to BcMath\Number
ValueError: Right string operand cannot be converted to BcMath\Number
ValueError: Right string operand cannot be converted to BcMath\Number
ValueError: Right string operand cannot be converted to BcMath\Number
ValueError: Right string operand cannot be converted to BcMath\Number
