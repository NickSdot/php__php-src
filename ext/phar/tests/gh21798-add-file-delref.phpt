--TEST--
GH-21798: phar_add_file must call phar_entry_delref on write error paths
--EXTENSIONS--
phar
--INI--
phar.readonly=0
phar.require_hash=0
--FILE--
<?php
// Regression baseline: verify addFile and addFromString succeed and
// phar_entry_delref is not skipped on the happy path.
$fname = TestPath::file('archive.phar')->path;

$phar = new Phar($fname);
$phar->addFromString('hello.txt', 'hello world');
$phar->addFromString('empty.txt', '');
unset($phar);

$phar = new Phar($fname);
echo $phar['hello.txt']->getContent() . "\n";
echo ($phar->offsetExists('empty.txt') ? 'empty exists' : 'missing') . "\n";
echo "no crash\n";
?>
--EXPECT--
hello world
empty exists
no crash
