--TEST--
Backed property with hooked readonly requires opt in
--FILE--
<?php

readonly class Test {
    public int $prop {
        get => $this->prop;
        set => $value;
    }
}

?>
--EXPECTF--
Fatal error: Readonly hooked properties must use readonly(set hooks) in %s on line %d
