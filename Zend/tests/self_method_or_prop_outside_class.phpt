--TEST--
Accessing self:: properties or methods outside a class
--FILE--
<?php

$fn = function() {
    $str = "foo";
    try {
        self::${$str . "bar"};
    } catch (Error $e) {
        echo $e::class, ': ', $e->getMessage(), PHP_EOL;
    }
    try {
        unset(self::${$str . "bar"});
    } catch (Error $e) {
        echo $e::class, ': ', $e->getMessage(), PHP_EOL;
    }
    try {
        isset(self::${$str . "bar"});
    } catch (Error $e) {
        echo $e::class, ': ', $e->getMessage(), PHP_EOL;
    }
    try {
        self::{$str . "bar"}();
    } catch (Error $e) {
        echo $e::class, ': ', $e->getMessage(), PHP_EOL;
    }
};
$fn();

?>
--EXPECT--
Error: Cannot access "self" when no class scope is active
Error: Cannot access "self" when no class scope is active
Error: Cannot access "self" when no class scope is active
Error: Cannot access "self" when no class scope is active
