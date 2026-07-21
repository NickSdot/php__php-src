--TEST--
Check that arguments are freed when a call to an abstract method throws
--FILE--
<?php

abstract class Test {
    abstract static function method();
}

try {
    Test::method(new stdClass);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

$ret = new stdClass;
try {
    $ret = Test::method(new stdClass);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Cannot call abstract method Test::method()
Error: Cannot call abstract method Test::method()
