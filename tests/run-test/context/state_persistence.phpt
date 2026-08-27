--TEST--
Runner initialises TestContext state before executing PHPT sections
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');

var_dump(is_file($stateFile));

TestPath::file();

var_dump(
    unserialize(file_get_contents($stateFile))
    instanceof TestContext,
);

?>
--EXPECT--
bool(true)
bool(true)
