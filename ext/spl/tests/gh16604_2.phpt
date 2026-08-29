--TEST--
GH-16604 (Memory leaks in SPL constructors) - SplFileObject
--FILE--
<?php

$file = TestPath::file('gh16604_2.tmp')->path;
file_put_contents($file, 'hello');

$obj = new SplFileObject($file);
try {
    $obj->__construct($file);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
Error: Cannot call constructor twice
