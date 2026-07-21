--TEST--
Acquire callable to assert()
--FILE--
<?php

namespace Foo;

$assert = assert(...);
$assert(1 == 1.0, "Message 1");
try {
    $assert(1 == 2.0, "Message 2");
} catch (\AssertionError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    assert(false && strlen(...));
} catch (\AssertionError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
AssertionError: Message 2
AssertionError: assert(false && strlen(...))
