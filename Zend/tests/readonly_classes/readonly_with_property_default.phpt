--TEST--
Properties of a readonly class may have default values
--FILE--
<?php

readonly class Foo
{
    public int $bar = 1;
    public ?string $nullable = null;

    public function __construct()
    {
        try {
            $this->bar = 2;
        } catch (Error $e) {
            echo $e::class, ": ", $e->getMessage(), "\n";
        }
    }
}

$foo = new Foo();
var_dump($foo->bar);
var_dump($foo->nullable);

try {
    $foo->bar = 3;
} catch (Error $e) {
    echo $e::class, ": ", $e->getMessage(), "\n";
}

?>
--EXPECT--
Error: Cannot modify readonly property Foo::$bar
int(1)
NULL
Error: Cannot modify readonly property Foo::$bar
