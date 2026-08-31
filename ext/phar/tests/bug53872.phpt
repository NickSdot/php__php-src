--TEST--
bug#53872 (internal corruption of phar)
--EXTENSIONS--
phar
zlib
--INI--
phar.readonly=0
--FILE--
<?php
$archive = Workspace::file('bug53872-phar.phar');
$p=new Phar($archive);
$p->buildFromDirectory(__DIR__ . "/bug53872/");
$p->setStub('<?php __HALT_COMPILER();?\>');
$p->compressFiles(Phar::GZ);

$uri = "phar://$archive";
print(file_get_contents($uri . '/first.txt'));
print(file_get_contents($uri . '/second.txt'));
print(file_get_contents($uri . '/third.txt'));
?>
--EXPECT--
content of first.txt
content of third.txt
