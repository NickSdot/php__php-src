--TEST--
Some string offset errors
--FILE--
<?php

function &test() : string {
    $str = "foo";
    return $str[0];
}

function &gen() {
    $str = "foo";
    yield $str[0];
}

try {
    test();
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $str = "foo";
    $str[0] =& $str[1];
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    foreach (gen() as $v) {}
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Cannot create references to/from string offsets
Error: Cannot create references to/from string offsets
Error: Cannot create references to/from string offsets
