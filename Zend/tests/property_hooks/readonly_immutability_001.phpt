--TEST--
Readonly property hooks always return the same value
--FILE--
<?php
class Unusual
{
    public function __construct(
        public readonly(set hooks) int $value {
            get => $this->value * random_int(1, 100);
        }
    ) {}
}

$unusual = new Unusual(1);

var_dump($unusual->value === $unusual->value);
?>
--EXPECT--
bool(true)
