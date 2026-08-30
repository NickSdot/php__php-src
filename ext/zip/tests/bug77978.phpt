--TEST--
Bug #77978 (Dirname ending in colon unzips to wrong dir)
--EXTENSIONS--
zip
--FILE--
<?php
TestContext::useRelativeWorkspace();
$archive = TestPath::file();
$targetDirectory = TestPath::directory();
$target = $targetDirectory->path;
$dir = $targetDirectory->directory('dir');
$colon = $dir->directory('test:');
$extracted = $colon->file('filename.txt');

mkdir($target);

$zip = new ZipArchive();
$zip->open($archive, ZipArchive::CREATE|ZipArchive::OVERWRITE);
$zip->addFromString("dir/test:/filename.txt", "contents");
$zip->close();

$zip->open($archive);
// Windows won't extract filenames with colons; we suppress the warning
@$zip->extractTo($target, "dir/test:/filename.txt");
$zip->close();

var_dump(!file_exists("$target/filename.txt"));
var_dump(PHP_OS_FAMILY === "Windows" || file_exists("$target/dir/test:/filename.txt"));
?>
--EXPECT--
bool(true)
bool(true)
