--TEST--
Phar: Phar::extractTo() - .phar safety
--EXTENSIONS--
phar
--INI--
phar.readonly=0
--FILE--
<?php

$archive = TestPath::file('tempmanifest2.phar.php');
$fname = $archive->path;
$pname = 'phar://' . $fname;

$extract = TestPath::directory('extract2');
mkdir($extract->path);
$extract->file('file1.txt');
$extract->file('file2.txt');
$subdir = $extract->directory('subdir');
$ectory = $subdir->directory('ectory');
$ectory->file('file.txt');
$one = $extract->directory('one');
$one->directory('level');

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
