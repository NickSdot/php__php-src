--TEST--
Readonly classes cannot use dynamic properties
--FILE--
<?php

readonly class Foo
{
}

$foo = new Foo();

try {
    $foo->bar = 1;
} catch (Error $exception) {
    echo $exception::class, ': ', $exception->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Cannot create dynamic property Foo::$bar
