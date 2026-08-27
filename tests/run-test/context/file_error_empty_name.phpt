--TEST--
TestPath::file() rejects an empty name
--FILE--
<?php

try {
    TestPath::file('');
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
ValueError: Name cannot be empty
