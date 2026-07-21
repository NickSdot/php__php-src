--TEST--
BackedEnum::from() reject invalid int
--FILE--
<?php

enum Foo: int {
    case Bar = 0;
    case Baz = 1;
}

try {
    var_dump(Foo::from(2));
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
ValueError: 2 is not a valid backing value for enum Foo
