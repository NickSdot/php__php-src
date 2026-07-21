--TEST--
Backed enums reject duplicate int values
--FILE--
<?php

enum Foo: int {
    case Bar = 0;
    case Baz = 0;
}

try {
    var_dump(Foo::Bar);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    var_dump(Foo::Bar);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    var_dump(Foo::from(42));
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    var_dump(Foo::tryFrom('bar'));
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Duplicate value in enum Foo for cases Bar and Baz
Error: Duplicate value in enum Foo for cases Bar and Baz
Error: Duplicate value in enum Foo for cases Bar and Baz
TypeError: Foo::tryFrom(): Argument #1 ($value) must be of type int, string given
