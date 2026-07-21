--TEST--
Calling assert with named params
--FILE--
<?php

assert(assertion: true);
try {
    assert(assertion: false);
} catch (AssertionError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

assert(assertion: true, description: "Description");
try {
    assert(assertion: false, description: "Description");
} catch (AssertionError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    assert(description: "Description");
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
AssertionError: assert(assertion: false)
AssertionError: Description
Error: Named parameter $description overwrites previous argument
