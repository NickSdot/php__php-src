--TEST--
Test context bootstrap - SKIPIF - managed files persist into FILE
--SKIPIF--
<?php

file_put_contents(Workspace::file('fixture.txt'), 'skipif');
echo 'nocache';

?>
--FILE--
<?php

var_dump(file_get_contents(Workspace::file('fixture.txt')));

?>
--EXPECT--
string(6) "skipif"
