--TEST--
AST printing for closures in attributes
--FILE--
<?php

// Do not use `false &&` to fully evaluate the function / class definition.

try {
    \assert(
        !
        #[Attr(static function ($foo) {
            echo $foo;
        })]
        function () { }
    );
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    \assert(
        !
        new #[Attr(static function ($foo) {
            echo $foo;
        })]
        class {}
    );
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
AssertionError: assert(!#[Attr(static function ($foo) {
    echo $foo;
})] function () {
})
AssertionError: assert(!new #[Attr(static function ($foo) {
    echo $foo;
})] class {
})
