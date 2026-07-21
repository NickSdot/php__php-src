--TEST--
Asymmetric visibility reference in forbidden scope
--FILE--
<?php

class C {
    public private(set) int $prop = 1;
}

function test($c) {
    $prop = &$c->prop;
    $prop = 42;
}

try {
    test(new C());
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    test(new C());
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Cannot indirectly modify private(set) property C::$prop from global scope
Error: Cannot indirectly modify private(set) property C::$prop from global scope
