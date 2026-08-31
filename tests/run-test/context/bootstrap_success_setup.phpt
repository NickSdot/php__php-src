--TEST--
Test context bootstrap - SETUP - managed files persist into FILE
--SETUP--
<?php

file_put_contents(Workspace::file('fixture.txt'), 'setup');

?>
--FILE--
<?php

var_dump(file_get_contents(Workspace::file('fixture.txt')));

?>
--EXPECT--
string(5) "setup"
