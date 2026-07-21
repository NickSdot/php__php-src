--TEST--
Bug #70918 (Segfault using static outside of class scope)
--FILE--
<?php
try {
    static::x;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    parent::x;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    self::x;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    new static;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    static::x();
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    static::$i;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECT--
Error: Cannot access "static" when no class scope is active
Error: Cannot access "parent" when no class scope is active
Error: Cannot access "self" when no class scope is active
Error: Cannot access "static" when no class scope is active
Error: Cannot access "static" when no class scope is active
Error: Cannot access "static" when no class scope is active
