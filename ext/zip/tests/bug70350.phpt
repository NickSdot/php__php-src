--TEST--
Bug #70350 (ZipArchive::extractTo allows for directory traversal when creating directories)
--EXTENSIONS--
zip
--FILE--
<?php

$directory = Workspace::directory()->create();
$dir = $directory->path;
$archiveFile = $directory->file('a.zip')->path;
$archive = new ZipArchive();
$archive->open($archiveFile,ZipArchive::CREATE);
$archive->addEmptyDir("../down2/");
$archive->close();

$archive2 = new ZipArchive();
$archive2->open($archiveFile);
$archive2->extractTo($dir);
$archive2->close();
var_dump(file_exists("$dir/down2/"));
var_dump(file_exists("../down2/"));
?>
--EXPECT--
bool(true)
bool(false)
