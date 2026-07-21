--TEST--
Test failing readonly assignment with coercion
--FILE--
<?php

class Foo {
    public readonly string $bar;

    public function __construct() {
        $this->bar = 'bar';
        try {
            $this->bar = 42;
        } catch (Error $e) {
            echo $e::class, ': ', $e->getMessage(), PHP_EOL;
        }
    }
}

new Foo();

?>
--EXPECTF--
Error: Cannot modify readonly property Foo::$bar
