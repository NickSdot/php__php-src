--TEST--
Phar: Phar::extractTo() - .phar safety
--EXTENSIONS--
phar
--INI--
phar.readonly=0
--FILE--
<?php

$archive = Workspace::file('tempmanifest2.phar.php');
$fname = $archive->path;
$pname = 'phar://' . $fname;

$extract = Workspace::directory('extract2')->create();

$phar = new Phar($fname);
$phar->setDefaultStub();
$phar->setAlias('fred');
$phar['file1.txt'] = 'hi';
$phar['file2.txt'] = 'hi2';
$phar['subdir/ectory/file.txt'] = 'hi3';
$phar->mount($pname . '/mount2', __FILE__);
$phar->addEmptyDir('one/level');

$phar->extractTo($extract->path, 'mount2');
$phar->extractTo($extract->path);
$out = array();

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extract->path, 0x00003000), RecursiveIteratorIterator::CHILD_FIRST) as $path => $file) {
    $extracted[] = $path;
}

sort($extracted);

foreach ($extracted as $out) {
    echo "$out\n";
}

?>
--EXPECTF--
%sextract2%cfile1.txt
%sextract2%cfile2.txt
%sextract2%cone
%sextract2%csubdir
%sextract2%csubdir%cectory
%sextract2%csubdir%cectory%cfile.txt
