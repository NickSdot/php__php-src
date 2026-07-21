--TEST--
Freeing of function "name" when dynamic call fails
--FILE--
<?php

try {
    $bar = "bar";
    ("foo" . $bar)();
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $bar = ["bar"];
    (["foo"] + $bar)();
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    (new stdClass)();
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Call to undefined function foobar()
Error: Array callback must have exactly two elements
Error: Object of type stdClass is not callable
