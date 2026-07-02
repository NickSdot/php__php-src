--TEST--
namespace name as scalar from ::namespace error using parent in global scope
--FILE--
<?php

$x = parent::namespace;

?>
--EXPECTF--
Fatal error: Uncaught Error: Cannot use "parent" in the global scope in %s:%d
Stack trace:
#0 {main}
  thrown in %s on line 3
