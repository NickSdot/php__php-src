--TEST--
Virtual readonly property cannot have hooks
--FILE--
<?php

class Test {
    public readonly int $prop {
        get => 42;
    }
}
?>
--EXPECTF--
Fatal error: Hooked virtual properties cannot be readonly in %s on line %d
