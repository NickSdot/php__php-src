--TEST--
Crash when using dynamic call syntax with fully qualified name in a namespace
--FILE--
<?php

namespace Foo;
try {
    ('\bar')();
} catch (\Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Call to undefined function bar()
