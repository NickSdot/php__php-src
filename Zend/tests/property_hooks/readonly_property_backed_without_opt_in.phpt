--TEST--
Backed readonly property with hooks requires opt-in
--FILE--
<?php

class Test {
    public readonly int $prop {
        get => $this->prop;
        set => $value;
    }
}

?>
--EXPECTF--
Fatal error: Readonly hooked properties must use readonly(set hooks) in %s on line %d
