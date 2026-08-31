--TEST--
Bug #52508 (newline problem with parse_ini_file+INI_SCANNER_RAW)
--FILE--
<?php

$file = Workspace::file();

file_put_contents($file, "a = 1");

$ini_array = parse_ini_file($file, true, INI_SCANNER_RAW);
var_dump($ini_array);

?>
--EXPECT--
array(1) {
  ["a"]=>
  string(1) "1"
}
