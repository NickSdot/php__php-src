--TEST--
Call property hooks by name
--FILE--
<?php

class Test {
    public $prop {
        get { echo "get called\n"; }
        set { echo "set called with $value\n"; }
    }
}

$test = new Test;
try {
    $test->{'$prop::get'}();
} catch (\Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $test->{'$prop::set'}('foo');
} catch (\Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Call to undefined method Test::$prop::get()
Error: Call to undefined method Test::$prop::set()
