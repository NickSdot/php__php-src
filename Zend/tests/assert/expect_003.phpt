--TEST--
test catching failed assertion
--INI--
zend.assertions=1
assert.exception=1
--FILE--
<?php
try {
    assert(false);
} catch (AssertionError $ex) {
    echo $ex::class, ': ', $ex->getMessage(), PHP_EOL;
}
?>
--EXPECT--
AssertionError: assert(false)
