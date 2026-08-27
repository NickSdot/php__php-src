--TEST--
Context bootstrap preserves source line numbers in PHPT sections
--FILE--
<?php

echo __LINE__, "\n";

?>
--EXPECT--
3
