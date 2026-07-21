--TEST--
func_num_args() outside of a function declaration
--FILE--
<?php

try {
    func_num_args();
} catch (Error $exception) {
    echo $exception::class, ': ', $exception->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: func_num_args() must be called from a function context
