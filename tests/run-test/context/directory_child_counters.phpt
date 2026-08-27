--TEST--
Directory child paths are counted independently for each parent
--FILE--
<?php

$first = TestPath::directory('first');
$second = TestPath::directory('second');

var_dump($first->file()->name);
var_dump($second->file()->name);
var_dump($first->directory()->name);
var_dump($second->directory()->name);

?>
--EXPECT--
string(6) "file-1"
string(6) "file-1"
string(11) "directory-1"
string(11) "directory-1"
