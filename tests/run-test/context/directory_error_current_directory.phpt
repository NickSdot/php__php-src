--TEST--
TestPath::directory() rejects the current-directory name
--FILE--
<?php

try {
    TestPath::directory('.');
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
ValueError: Name cannot be . or ..
