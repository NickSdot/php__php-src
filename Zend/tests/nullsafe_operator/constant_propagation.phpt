--TEST--
Constant propagation with nullsafe operator
--FILE--
<?php

class Bar { const FOO = "foo"; }

try {
    Bar::FOO?->length();
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Call to a member function length() on string
