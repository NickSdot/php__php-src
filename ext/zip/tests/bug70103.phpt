--TEST--
Bug #70103 (ZipArchive::addGlob ignores remove_all_path option)
--EXTENSIONS--
zip
--FILE--
<?php
$directory = TestPath::directory('bug70103');
$dir = $directory->path;
$foo = $directory->file('foo.txt')->path;
$archive = $directory->file('test.zip')->path;

mkdir($dir); chmod($dir, 0777);
file_put_contents($foo, 'foo');

$zip = new ZipArchive();
$zip->open($dir . '/test.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
$zip->addGlob($dir . '/*.txt', GLOB_NOSORT, array('remove_all_path' => true));
$zip->close();

$zip = new ZipArchive();
$zip->open($archive);
var_dump($zip->numFiles);
var_dump($zip->getNameIndex(0));
$zip->close();
?>
--EXPECT--
int(1)
string(7) "foo.txt"
