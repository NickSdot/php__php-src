--TEST--
Enum properties cannot be unset
--FILE--
<?php

enum Foo {
    case Bar;
}

enum IntFoo: int {
    case Bar = 0;
}

$foo = Foo::Bar;
try {
    unset($foo->name);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

$intFoo = IntFoo::Bar;
try {
    unset($intFoo->name);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    unset($intFoo->value);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Cannot unset readonly property Foo::$name
Error: Cannot unset readonly property IntFoo::$name
Error: Cannot unset readonly property IntFoo::$value
