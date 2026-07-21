--TEST--
Calling a static method on a non-existing class
--FILE--
<?php

$str = "foo";
try {
    Test::{$str . "bar"}();
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Class "Test" not found
