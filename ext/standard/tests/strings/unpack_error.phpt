--TEST--
Test unpack() function : error conditions
--FILE--
<?php

try {
    var_dump(unpack("B", pack("I", 65534)));
} catch (ValueError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
ValueError: Invalid format type B
