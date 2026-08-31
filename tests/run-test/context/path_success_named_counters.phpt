--TEST--
Test Workspace allocation - named paths - anonymous counters are unchanged
--FILE--
<?php

Workspace::file('fixture.txt');
Workspace::directory('fixtures');

var_dump(Workspace::file()->name);
var_dump(Workspace::directory()->name);

?>
--EXPECT--
string(6) "file-1"
string(11) "directory-1"
