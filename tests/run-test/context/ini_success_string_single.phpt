--TEST--
Workspace accepts a single INI setting as a string
--SETUP--
<?php

Workspace::ini('precision = 12');

?>
--FILE--
<?php

var_dump(ini_get('precision'));

?>
--EXPECT--
string(2) "12"
