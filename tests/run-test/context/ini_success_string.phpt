--TEST--
Workspace INI settings supplied as a string
--SETUP--
<?php

Workspace::ini(<<<INI
    date.timezone = UTC; this is a comment
    precision = 12
    arg_separator.output = "a;b,c"
    INI);

?>
--FILE--
<?php

var_dump(ini_get('date.timezone'));
var_dump(ini_get('precision'));
var_dump(ini_get('arg_separator.output'));

?>
--EXPECT--
string(3) "UTC"
string(2) "12"
string(5) "a;b,c"
