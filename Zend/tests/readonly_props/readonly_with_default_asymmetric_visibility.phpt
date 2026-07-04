--TEST--
Readonly property with default value and asymmetric visibility
--FILE--
<?php

class Test {
    public readonly int $default = 1;
    public private(set) readonly int $private = 2;
    public protected(set) readonly int $protected = 3;
    public public(set) readonly int $public = 4;
}

$test = new Test();
var_dump($test->default, $test->private, $test->protected, $test->public);

foreach (['default', 'private', 'protected', 'public'] as $prop) {
    try {
        $test->$prop = 42;
    } catch (Error $e) {
        echo $e->getMessage(), "\n";
    }
}

?>
--EXPECT--
int(1)
int(2)
int(3)
int(4)
Cannot modify readonly property Test::$default
Cannot modify readonly property Test::$private
Cannot modify readonly property Test::$protected
Cannot modify readonly property Test::$public
