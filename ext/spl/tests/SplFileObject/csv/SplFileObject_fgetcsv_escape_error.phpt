--TEST--
SplFileObject::fgetcsv() escape error
--FILE--
<?php
$file = Workspace::file();
$fp = fopen($file, 'w+');
fwrite($fp, '"aaa","b""bb","ccc"');
fclose($fp);

$fo = new SplFileObject($file);
try {
    var_dump($fo->fgetcsv(',', '"', 'invalid'));
} catch (ValueError $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}
?>
--EXPECTF--
Deprecated: Method SplFileObject::fgetcsv() is deprecated since 8.6 in %s on line %d
ValueError: SplFileObject::fgetcsv(): Argument #3 ($escape) must be empty or a single character
