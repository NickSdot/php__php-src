--TEST--
TestContext returns one instance within each PHP process
--FILE--
<?php

var_dump(TestContext::instance() === TestContext::instance());

?>
--EXPECT--
bool(true)
