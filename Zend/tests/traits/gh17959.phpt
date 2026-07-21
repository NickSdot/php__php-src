--TEST--
GH-17959: Missing trait error is recoverable
--FILE--
<?php

try {
    class C {
        use MissingTrait;
    }
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
===DONE===
--EXPECT--
Error: Trait "MissingTrait" not found
===DONE===
