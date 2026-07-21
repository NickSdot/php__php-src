--TEST--
TypeError for compound assignment operations
--FILE--
<?php

$x = [];
try {
    $x += "1";
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $x -= "1";
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $x *= "1";
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $x /= "1";
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $x **= "1";
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $x %= "1";
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $x <<= "1";
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $x >>= "1";
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
TypeError: Unsupported operand types: array + string
TypeError: Unsupported operand types: array - string
TypeError: Unsupported operand types: array * string
TypeError: Unsupported operand types: array / string
TypeError: Unsupported operand types: array ** string
TypeError: Unsupported operand types: array % string
TypeError: Unsupported operand types: array << string
TypeError: Unsupported operand types: array >> string
