--TEST--
Enum disallows cloning
--FILE--
<?php

enum Foo {
    case Bar;
}

try {
    var_dump(clone Foo::Bar);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Trying to clone an uncloneable object of class Foo
