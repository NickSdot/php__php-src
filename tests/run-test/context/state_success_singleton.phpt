--TEST--
Test TestContext::instance() - returns the same object within a process
--FILE--
<?php

use TestSuite\Context\TestContext;

var_dump(TestContext::instance() === TestContext::instance());

?>
--EXPECT--
bool(true)
