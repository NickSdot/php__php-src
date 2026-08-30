--TEST--
SplFileObject::fgetcsv with default escape character
--FILE--
<?php
$file = TestPath::file();
$fp = fopen($file, 'w+');
fwrite($fp, '"aa\"","bb","\"c"');
fclose($fp);

$fo = new SplFileObject($file);
var_dump($fo->fgetcsv());
?>
--EXPECTF--
Deprecated: Method SplFileObject::fgetcsv() is deprecated since 8.6 in %s on line %d

Deprecated: SplFileObject::fgetcsv(): the $escape parameter must be provided, as its default value will change, either explicitly or via SplFileObject::setCsvControl() in %s on line %d
array(3) {
  [0]=>
  string(4) "aa\""
  [1]=>
  string(2) "bb"
  [2]=>
  string(3) "\"c"
}
