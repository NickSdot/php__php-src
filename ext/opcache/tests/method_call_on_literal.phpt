--TEST--
Literal compaction should take method calls on literals into account
--EXTENSIONS--
opcache
--FILE--
<?php

try {
    (42)->foo();
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Call to a member function foo() on int
