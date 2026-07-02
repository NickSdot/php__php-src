--TEST--
namespace name as scalar from ::namespace error using static in global scope
--FILE--
<?php

$x = static::namespace;

?>
--EXPECTF--
Fatal error: Uncaught Error: Cannot use "static" in the global scope in %s:%d
Stack trace:
#0 {main}
  thrown in %s on line 3
