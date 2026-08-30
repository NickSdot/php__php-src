--TEST--
SplFileObject::fgetcsv() enclosure error
--FILE--
<?php
$file = TestPath::file('SplFileObject__fgetcsv5.csv')->path;
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
try {
    var_dump($fo->fgetcsv(enclosure: 'invalid'));
} catch (ValueError $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}
?>
--EXPECTF--
Deprecated: Method SplFileObject::setCsvControl() is deprecated since 8.6 in %s on line %d

Deprecated: Method SplFileObject::fgetcsv() is deprecated since 8.6 in %s on line %d
ValueError: SplFileObject::fgetcsv(): Argument #2 ($enclosure) must be a single character
