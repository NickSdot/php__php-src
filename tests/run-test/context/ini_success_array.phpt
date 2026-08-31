--TEST--
Workspace INI settings supplied as an array
--SETUP--
<?php

Workspace::ini([
    'memory_limit' => '-1',
    'date.timezone' => 'UTC',
]);

?>
--FILE--
<?php

var_dump(ini_get('memory_limit'));
var_dump(ini_get('date.timezone'));

?>
--EXPECT--
string(2) "-1"
string(3) "UTC"
