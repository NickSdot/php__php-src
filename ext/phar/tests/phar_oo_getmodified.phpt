--TEST--
Phar::getModified()
--EXTENSIONS--
phar
zlib
--INI--
phar.require_hash=0
phar.readonly=0
--FILE--
<?php
$fname = Workspace::file('archive.phar.php')->path;
$pname = 'phar://' . $fname;
$file = '<?php __HALT_COMPILER(); ?>';

$files = array();
$files['a'] = 'a';
$files['b'] = 'b';
$files['c'] = 'c';

include 'files/phar_test.inc';

$phar = new Phar($fname);
var_dump($phar->getModified());
$phar->compressFiles(Phar::GZ);
var_dump($phar->getModified());
?>
--EXPECT--
bool(false)
bool(true)
