--TEST--
Backed readonly property may have hooks
--FILE--
<?php

// readonly property
class Test1 {
    public readonly int $prop {
        get => $this->prop;
        set => $value;
    }

    public function __construct(int $v) {
        $this->prop = $v;
    }
}

$t = new Test1(42);
var_dump($t->prop);
try {
    $t->prop = 43;
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}

// readonly class
readonly class Test2 {
    public int $prop {
        get => $this->prop;
        set => $value;
    }

    public function __construct(int $v) {
        $this->prop = $v;
    }
}

$t = new Test2(42);
var_dump($t->prop);
try {
    $t->prop = 43;
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}

// readonly class, promoted
readonly class Test3 {
    public function __construct(
        public int $prop {
            get => $this->prop;
            set => $value;
        }
    ) {}
}

$t = new Test3(42);
var_dump($t->prop);
try {
    $t->prop = 43;
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}
?>
--EXPECT--
int(42)
Cannot modify protected(set) readonly property Test1::$prop from global scope
int(42)
Cannot modify protected(set) readonly property Test2::$prop from global scope
int(42)
Cannot modify protected(set) readonly property Test3::$prop from global scope
