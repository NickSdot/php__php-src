--TEST--
ZipArchive::getFromIndex() honors FL_UNCHANGED for deleted entries
--EXTENSIONS--
zip
--FILE--
<?php
$name = TestPath::file('bug_gh21705.zip')->path;
@unlink($name);
$zip = new ZipArchive;
$zip->open($name, ZipArchive::CREATE);
$zip->addFromString('foo.txt', 'foo');
$zip->addFromString('bar.txt', 'bar');
$zip->close();

$zip = new ZipArchive;
$zip->open($name);

$index = $zip->locateName('bar.txt');
$zip->deleteName('bar.txt');

var_dump($zip->getFromName('bar.txt', 0, ZipArchive::FL_UNCHANGED));
var_dump($zip->getFromIndex($index, 0, ZipArchive::FL_UNCHANGED));

$zip->close();
?>
--EXPECT--
string(3) "bar"
string(3) "bar"
