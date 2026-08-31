--TEST--
SplFileObject::fgetcsv default path
--FILE--
<?php
$file = Workspace::file();
$fp = fopen($file, 'w+');
fputcsv(
    $fp,
    [
        'field1',
        'field2',
        'field3',
        5,
    ],
    escape: '',
);
fclose($fp);

$fo = new SplFileObject($file);
$fo->setCsvControl(escape: '');
var_dump($fo->fgetcsv());
?>
--EXPECTF--
Deprecated: Method SplFileObject::setCsvControl() is deprecated since 8.6 in %s on line %d

Deprecated: Method SplFileObject::fgetcsv() is deprecated since 8.6 in %s on line %d
array(4) {
  [0]=>
  string(6) "field1"
  [1]=>
  string(6) "field2"
  [2]=>
  string(6) "field3"
  [3]=>
  string(1) "5"
}
