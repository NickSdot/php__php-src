--TEST--
Testing indirect method call and exceptions
--FILE--
<?php

class foo {
    public function __construct() {
        throw new Exception('foobar');
    }
}

try {
    $X = (new foo)->Inexistent(3);
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL; // foobar
}

?>
--EXPECT--
Exception: foobar
