--TEST--
(void) is included in AST printing
--FILE--
<?php

try {
    assert(false && function () {
        (void) somefunc();
    });
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
AssertionError: assert(false && function () {
    (void)somefunc();
})
