--TEST--
SETUP bootstrap exposes TestContext without exposing the FILE-only TestPath utility
--SETUP--
<?php

if (class_exists('TestPath', false)) {
    throw new Exception('TestPath is available in SETUP');
}

TestContext::useRelativeWorkspace();

?>
--FILE--
<?php

var_dump(class_exists('TestPath', false));

?>
--EXPECT--
bool(true)
