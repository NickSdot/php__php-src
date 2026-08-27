--TEST--
TestPath::file() rejects a name containing a backslash
--FILE--
<?php

try {
    TestPath::file('directory\\file');
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
ValueError: Name cannot contain directory separators
