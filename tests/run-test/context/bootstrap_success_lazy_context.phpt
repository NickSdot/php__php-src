--TEST--
Test context bootstrap - context loading does not consume an object ID before Workspace is used
--FILE--
<?php

$object = new stdClass();
var_dump(spl_object_id($object));

$file = Workspace::file();
var_dump($file->name);

?>
--EXPECT--
int(1)
string(6) "file-1"
