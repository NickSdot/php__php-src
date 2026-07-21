--TEST--
Trying to use lambda as array key
--FILE--
<?php

try {
    var_dump(array(function() { } => 1));
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
TypeError: Cannot access offset of type Closure on array
