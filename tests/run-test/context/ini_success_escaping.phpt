--TEST--
Workspace INI array values preserve INI metacharacters
--SETUP--
<?php

Workspace::ini(['arg_separator.output' => 'a;"b"']);

?>
--FILE--
<?php

var_dump(ini_get('arg_separator.output') === 'a;"b"');

?>
--EXPECT--
bool(true)
