--TEST--
Bug #60222 (time_nanosleep() does validate input params)
--FILE--
<?php
    try {
        time_nanosleep(-1, 0);
    } catch (ValueError $exception) {
        echo $exception::class, ': ', $exception->getMessage(), PHP_EOL;
    }

    try {
        time_nanosleep(0, -1);
    } catch (ValueError $exception) {
        echo $exception::class, ': ', $exception->getMessage(), PHP_EOL;
    }
?>
--EXPECT--
ValueError: time_nanosleep(): Argument #1 ($seconds) must be greater than or equal to 0
ValueError: time_nanosleep(): Argument #2 ($nanoseconds) must be greater than or equal to 0
