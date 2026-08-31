--TEST--
Workspace INI settings are available to CGI
--SETUP--
<?php

Workspace::ini(['date.timezone' => 'UTC']);

?>
--CGI--
--FILE--
<?php

var_dump(ini_get('date.timezone'));

?>
--EXPECT--
string(3) "UTC"
