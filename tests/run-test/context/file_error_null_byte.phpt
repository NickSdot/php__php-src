--TEST--
TestPath::file() rejects a name containing a null byte
--FILE--
<?php

try {
    TestPath::file("invalid\0name");
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
ValueError: Name cannot contain null bytes
