--TEST--
ReflectionConstant on inexistent constant
--FILE--
<?php

try {
    new \ReflectionConstant('C');
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
ReflectionException: Constant "C" does not exist
