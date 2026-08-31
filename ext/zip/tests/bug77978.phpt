--TEST--
Bug #77978 (Dirname ending in colon unzips to wrong dir)
--EXTENSIONS--
zip
--FILE--
<?php
Workspace::useRelative();
$archive = Workspace::file();
$targetDirectory = Workspace::directory()->create();
$target = $targetDirectory->path;

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
