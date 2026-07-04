--TEST--
Readonly property with default value satisfies get-only interface property
--FILE--
<?php

interface I {
    public int $prop { get; }
}

class C implements I {
    public readonly int $prop = 42;
}

var_dump(new C()->prop);

interface J {
    public int $prop { get; set; }
}

// does not satisfy set
class D implements J {
    public readonly int $prop = 42;
}

?>
--EXPECTF--
int(42)

Fatal error: Set access level of D::$prop must be omitted (as in class J) in %s on line %d
