--TEST--
Test context bootstrap - source line numbers are preserved
--FILE--
<?php

echo __LINE__, "\n";

?>
--EXPECT--
3
