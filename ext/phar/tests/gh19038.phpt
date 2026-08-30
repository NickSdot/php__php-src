--TEST--
GH-19038 (Phar crash and data corruption with SplFileObject)
--EXTENSIONS--
phar
--INI--
phar.readonly=0
--FILE--
<?php

$archive = TestPath::file('gh19038.phar');
$phar = new Phar($archive->path);
$phar->addFromString("file", "123");
$file = $phar["file"]->openFile();
$file->fseek(3);
var_dump($file->fwrite("456", 3));
$file->fseek(0);
echo $file->fread(100);

?>
--EXPECT--
int(3)
123456
