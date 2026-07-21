--TEST--
Bug #70557 (Memleak on return type verifying failed).
--FILE--
<?php

function getNumber() : int {
    return "foo";
}

try {
    getNumber();
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECT--
TypeError: getNumber(): Return value must be of type int, string returned
