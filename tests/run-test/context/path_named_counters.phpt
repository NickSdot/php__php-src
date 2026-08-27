--TEST--
Explicitly named paths do not advance anonymous path counters
--FILE--
<?php

TestPath::file('fixture.txt');
TestPath::directory('fixtures');

var_dump(TestPath::file()->name);
var_dump(TestPath::directory()->name);

?>
--EXPECT--
string(6) "file-1"
string(11) "directory-1"
