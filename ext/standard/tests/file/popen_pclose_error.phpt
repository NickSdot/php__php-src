--TEST--
Test popen() and pclose function: error conditions
--FILE--
<?php

try {
    popen("abc.txt", "x");
} catch (ValueError $exception) {
    echo $exception::class, ': ', $exception->getMessage(), PHP_EOL;
}

try {
    popen("abc.txt", "rw");
} catch (ValueError $exception) {
    echo $exception::class, ': ', $exception->getMessage(), PHP_EOL;
}

try {
    popen("abc.txt", "rwb");
} catch (ValueError $exception) {
    echo $exception::class, ': ', $exception->getMessage(), PHP_EOL;
}

?>
--EXPECT--
ValueError: popen(): Argument #2 ($mode) must be one of "r", "rb", "w", or "wb"
ValueError: popen(): Argument #2 ($mode) must be one of "r", "rb", "w", or "wb"
ValueError: popen(): Argument #2 ($mode) must be one of "r", "rb", "w", or "wb"
