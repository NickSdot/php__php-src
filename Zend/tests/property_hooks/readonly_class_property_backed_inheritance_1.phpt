--TEST--
Backed property cannot redeclare readonly as non-readonly property
--FILE--
<?php

readonly class ParentClass {
    public int $prop;
}

class Test extends ParentClass {
    public function __construct(
        public int $prop {
            get => $this->prop;
            set => $value;
        }
    ) {}
}

?>
--EXPECTF--
Fatal error: Non-readonly class Test cannot extend readonly class ParentClass in %s on line %d