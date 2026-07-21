--TEST--
Constants can be dereferenced as objects (even though they can't be objects)
--FILE--
<?php

const FOO = "foo";
class Bar { const FOO = "foo"; }

try {
    FOO->length();
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    Bar::FOO->length();
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Call to a member function length() on string
Error: Call to a member function length() on string
