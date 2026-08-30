--TEST--
Bug #73809 (Phar Zip parse crash - mmap fail)
--EXTENSIONS--
phar
zip
--FILE--
<?php
// create the ZIP to be tested
$zip = new ZipArchive;
$archive = TestPath::file('73809.zip');
$zip->open($archive->path, ZipArchive::CREATE);
$zip->addFromString('73809.txt', 'yada yada');
$zip->addFromString('.phar/signature.bin', str_repeat('*', 64 * 1024 + 1));
$zip->setCompressionName('.phar/signature.bin', ZipArchive::CM_STORE);
var_dump($zip->close());

try {
    $phar = new PharData($archive->path);
} catch (Exception $ex) {
    echo $ex::class, ': ', $ex->getMessage(), PHP_EOL;
}
?>
--EXPECTF--
bool(true)
UnexpectedValueException: phar error: signatures larger than 64 KiB are not supported in zip-based phar "%s"
