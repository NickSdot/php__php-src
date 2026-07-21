--TEST--
Passing unknown named args to a non-existing ctor
--FILE--
<?php

class Test {}

try {
    new stdClass(x: "nope");
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    new Test(x: "nope");
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Unknown named parameter $x
Error: Unknown named parameter $x
