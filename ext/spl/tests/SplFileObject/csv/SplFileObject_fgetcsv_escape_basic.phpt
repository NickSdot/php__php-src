--TEST--
SplFileObject::fgetcsv with alternative escape
--FILE--
<?php
$file = TestPath::file('SplFileObject__fgetcsv6.csv')->path;
$fp = fopen($file, 'w+');
fwrite($fp, '"aaa","b""bb","ccc"');
fclose($fp);

$fo = new SplFileObject($file);
var_dump($fo->fgetcsv(',', '"', '"'));
?>
--EXPECTF--
Deprecated: Method SplFileObject::fgetcsv() is deprecated since 8.6 in %s on line %d
array(3) {
  [0]=>
  string(3) "aaa"
  [1]=>
  string(4) "b"bb"
  [2]=>
  string(3) "ccc"
}
