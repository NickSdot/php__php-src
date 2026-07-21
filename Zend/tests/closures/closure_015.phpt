--TEST--
Closure 015: converting to string/unicode
--FILE--
<?php

$x = function() { return 1; };
try {
    print (string) $x;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    print $x;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Object of class Closure could not be converted to string
Error: Object of class Closure could not be converted to string
