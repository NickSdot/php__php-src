--TEST--
AST printing for FCC in attributes
--FILE--
<?php

// Do not use `false &&` to fully evaluate the function / class definition.

try {
    \assert(
        !
        #[Attr(strrev(...))]
        function () { }
    );
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    \assert(
        !
        new #[Attr(strrev(...))]
        class {}
    );
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
AssertionError: assert(!#[Attr(strrev(...))] function () {
})
AssertionError: assert(!new #[Attr(strrev(...))] class {
})
