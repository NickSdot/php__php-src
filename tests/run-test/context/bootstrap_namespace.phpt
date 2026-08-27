--TEST--
Context bootstrap makes TestPath available in namespaced PHPT sections
--FILE--
<?php

namespace PhpTestContext;

$file = \TestPath::file('namespaced.txt');

var_dump($file->name);

?>
--EXPECT--
string(14) "namespaced.txt"
