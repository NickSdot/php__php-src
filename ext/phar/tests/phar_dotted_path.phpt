--TEST--
Phar: create new Phar with broken.dirname in path
--EXTENSIONS--
phar
--INI--
default_charset=
phar.readonly=0
phar.require_hash=0
--FILE--
<?php

$directory = TestPath::directory('broken.dirname');
$dir = $directory->path;
mkdir($dir, 0777);

$archive = $directory->file('dotted_path.phar');
$fname = $archive->path;
$stub = Phar::createDefaultStub();
$file = $stub;

$files = array();
$files['a'] = 'this is a';
$files['b'] = 'this is b';

include 'files/phar_test.inc';

$phar = new Phar($fname);

foreach ($phar as $entry) {
    echo file_get_contents($entry)."\n";
}

?>
--EXPECT--
this is a
this is b
