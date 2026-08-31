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

$directory = Workspace::directory('broken.dirname')->create();

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
