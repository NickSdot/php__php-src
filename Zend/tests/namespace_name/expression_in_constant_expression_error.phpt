--TEST--
::namespace on an expression cannot be used inside constant expressions
--FILE--
<?php

const A = [0]::namespace;

?>
--EXPECTF--
Fatal error: (expression)::namespace cannot be used in constant expressions in %s on line %d
