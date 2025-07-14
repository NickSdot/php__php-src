--TEST--
Get hooks are not allowed in readonly
--FILE--
<?php

readonly class Test {
    public function __construct(
        public int $prop {
            get => 42;
        }
    ) {}
}

?>
--EXPECTF--
Fatal error: Readonly property Test::$prop cannot have a get hook in %s on line %d
