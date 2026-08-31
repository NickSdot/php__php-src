--TEST--
class constants as default function arguments and dynamically loaded classes
--FILE--
<?php

$class_data = <<<DATA
<?php
class test {
    const val = 1;
}
?>
DATA;

$filename = Workspace::file();
file_put_contents($filename, $class_data);

function foo($v = test::val) {
    var_dump($v);
}

include $filename;

foo();
foo(5);

echo "Done\n";
?>
--EXPECT--
int(1)
int(5)
Done
