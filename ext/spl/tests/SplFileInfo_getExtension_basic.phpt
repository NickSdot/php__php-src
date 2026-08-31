--TEST--
SPL: SplFileInfo::getExtension() basic test
--SKIPIF--
<?php
if (PHP_OS_FAMILY === "Windows") die("skip not for Windows");
?>
--FILE--
<?php
$file = md5('SplFileInfo::getExtension');
$exts = array('.txt', '.extension', '..', '.', '');
foreach ($exts as $ext) {
    $path = Workspace::file($file . $ext)->create()->path;
    $info = new SplFileInfo($path);
    var_dump($info->getExtension(), pathinfo($path, PATHINFO_EXTENSION));
}
?>
--EXPECT--
string(3) "txt"
string(3) "txt"
string(9) "extension"
string(9) "extension"
string(0) ""
string(0) ""
string(0) ""
string(0) ""
string(0) ""
string(0) ""
