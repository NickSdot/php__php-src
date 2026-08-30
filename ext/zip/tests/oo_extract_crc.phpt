--TEST--
ZipArchive::extractTo() and getFrom*() fail on a CRC-corrupted entry
--EXTENSIONS--
zip
--FILE--
<?php
$archive = TestPath::file('corrupt.zip');
$entry = TestPath::file('a.txt');
$payload = str_repeat('A', 64) . 'PAYLOAD-END';

$zip = new ZipArchive();
$zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE);
$zip->addFromString($entry->name, $payload);
$zip->setCompressionName($entry->name, ZipArchive::CM_STORE);
$zip->close();

$raw = file_get_contents($archive);
$raw[strpos($raw, 'AAAA') + 2] = 'Z';
file_put_contents($archive, $raw);

$zip = new ZipArchive();
$zip->open($archive);
var_dump($zip->extractTo($entry->directory, $entry->name));
var_dump($zip->getFromName($entry->name));
var_dump($zip->getFromIndex(0));
$zip->close();
?>
--EXPECTF--
Warning: ZipArchive::extractTo(): Cannot extract "a.txt": "CRC error" in %s on line %d
bool(false)

Warning: ZipArchive::getFromName(): Cannot read entry: CRC error in %s on line %d
bool(false)

Warning: ZipArchive::getFromIndex(): Cannot read entry: CRC error in %s on line %d
bool(false)
