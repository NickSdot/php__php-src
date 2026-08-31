--TEST--
SplFileObject::fgetcsv() delimiter error
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
try {
    var_dump($fo->fgetcsv('invalid'));
} catch (ValueError $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}
?>
--EXPECTF--
Deprecated: Method SplFileObject::setCsvControl() is deprecated since 8.6 in %s on line %d

Deprecated: Method SplFileObject::fgetcsv() is deprecated since 8.6 in %s on line %d
ValueError: SplFileObject::fgetcsv(): Argument #1 ($separator) must be a single character
