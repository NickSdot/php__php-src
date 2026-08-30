--TEST--
ZipArchive::getFrom*() rejects an entry with an inconsistent uncompressed size
--EXTENSIONS--
zip
--FILE--
<?php
$directory = TestPath::directory();
$dirname = $directory->path;
$file = $directory->file('inconsistent.zip')->path;
$entry = $directory->file('a.txt');
mkdir($dirname);

$zip = new ZipArchive();
$zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
$zip->addFromString('a.txt', str_repeat('A', 10));
$zip->setCompressionName('a.txt', ZipArchive::CM_STORE);
$zip->close();

/* Advertise 20 bytes in the central directory, but keep only 10 bytes. */
$raw = file_get_contents($file);
for ($i = 0, $length = strlen($raw); $i + 27 < $length; $i++) {
    if (substr($raw, $i, 4) === "PK\x01\x02") {
        $size = unpack('V', substr($raw, $i + 24, 4))[1];
        $raw = substr_replace($raw, pack('V', $size + 10), $i + 24, 4);
        break;
    }
}
file_put_contents($file, $raw);

$zip = new ZipArchive();
$zip->open($file);
var_dump($zip->getFromName('a.txt'));
var_dump($zip->getFromIndex(0));
$zip->close();
?>
--EXPECTF--
Warning: ZipArchive::getFromName(): Cannot read entry: Zip archive inconsistent%s
bool(false)

Warning: ZipArchive::getFromIndex(): Cannot read entry: Zip archive inconsistent%s
bool(false)
