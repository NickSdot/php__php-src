--TEST--
Bug #48768 (parse_ini_*() crashes with INI_SCANNER_RAW)
--FILE--
<?php

$ini_location = Workspace::file();

// Build ini data
$ini_data = <<< EOT
equal = "="

EOT;

// Save ini data to file
file_put_contents($ini_location, $ini_data);

var_dump(parse_ini_file($ini_location, false, INI_SCANNER_RAW));
var_dump(parse_ini_file($ini_location, false, INI_SCANNER_NORMAL));

?>
--EXPECT--
array(1) {
  ["equal"]=>
  string(1) "="
}
array(1) {
  ["equal"]=>
  string(1) "="
}
