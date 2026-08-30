--TEST--
pcntl_exec() 3
--EXTENSIONS--
pcntl
--FILE--
<?php
$file = TestPath::touch();
var_dump(pcntl_exec($file, array("foo","bar"), array("foo" => "bar")));
?>
--EXPECTF--
Warning: pcntl_exec(): Error has occurred: (errno %d) %s
bool(false)
