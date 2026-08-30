--TEST--
Bug GH-12661 (Inconsistency in ZipArchive::addGlob 'remove_path' Option Behavior)
--EXTENSIONS--
zip
--FILE--
<?php
include __DIR__ . '/utils.inc';

$file = TestPath::file()->create();

$zip = new ZipArchive();
$zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
$zip->addGlob(__FILE__, 0, ['remove_path' => 'bug_']);  // unchanged (bug is not a prefix)
$zip->addGlob(__FILE__, 0, ['remove_path' => dirname(__DIR__)]);
verify_entries($zip, [__FILE__, basename(__DIR__) . DIRECTORY_SEPARATOR . basename(__FILE__)]);
$zip->close();

?>
Done
--EXPECT--
Done
