--TEST--
Hooked child property can override non-hooked readonly parent property
--FILE--
<?php

class ParentClass {
    public function __construct(
        public readonly int $prop
    ) {}
}

class ChildClass extends ParentClass {

    public readonly int $prop {
        set => $value * 2;
    }

    public function setAgain() {
        $this->prop = 42;
    }
}

$t = new ChildClass(911);

var_dump($t);
var_dump($t->prop === $t->prop);

try {
    $t->setAgain(); // cannot write, readonly
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $t->prop = 43; // cannot write, visibility
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
object(ChildClass)#1 (1) {
  ["prop"]=>
  int(1822)
}
bool(true)
Error: Cannot modify readonly property ChildClass::$prop
Error: Cannot modify protected(set) readonly property ChildClass::$prop from global scope
