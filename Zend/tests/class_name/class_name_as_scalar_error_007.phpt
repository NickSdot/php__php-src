--TEST--
Cannot access self::class when no class scope is active
--FILE--
<?php

try {
    var_dump(self::class);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    var_dump([self::class]);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Cannot use "self" in the global scope
Error: Cannot use "self" in the global scope
