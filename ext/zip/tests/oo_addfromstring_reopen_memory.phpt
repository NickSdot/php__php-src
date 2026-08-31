--TEST--
ZipArchive::addFromString() buffers are released when the archive is closed
--EXTENSIONS--
zip
--FILE--
<?php
$filename = Workspace::file('archive.zip');
$blob = str_repeat('q', 200000);

$zip = new ZipArchive;
$start = memory_get_usage();

for ($i = 0; $i < 50; $i++) {
    $zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('entry.txt', $blob);
    $zip->close();
}

var_dump(memory_get_usage() - $start < 1000000);
?>
--EXPECT--
bool(true)
