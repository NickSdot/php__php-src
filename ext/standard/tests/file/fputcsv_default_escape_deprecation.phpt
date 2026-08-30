--TEST--
fputcsv(): Deprecation if using default escape arg
--FILE--
<?php
$line = "test1,test2";

$file = TestPath::file();

$data = ["test1", "test2"];

$fp = fopen($file, 'w');

fputcsv($fp, $data);
unset($fp);

var_dump(file_get_contents($file));

?>
--EXPECTF--
Deprecated: fputcsv(): the $escape parameter must be provided as its default value will change in %s on line %d
string(12) "test1,test2
"
