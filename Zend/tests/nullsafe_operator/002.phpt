--TEST--
Test nullsafe strict type check
--FILE--
<?php

try {
    false?->bar();
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    []?->bar();
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    (0)?->bar();
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    (0.0)?->bar();
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    ''?->bar();
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Call to a member function bar() on false
Error: Call to a member function bar() on array
Error: Call to a member function bar() on int
Error: Call to a member function bar() on float
Error: Call to a member function bar() on string
