--TEST--
Phar::convertToTar()
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
phar.readonly=0
--FILE--
<?php

$archive = TestPath::file('phar_convert_tar.phar');
$fname = $archive->path;
$pname = 'phar://' . $fname;
$converted = TestPath::file('phar_convert_tar.phar.tar');
$fname2 = $converted->path;
$copy = TestPath::file('phar_convert_tar.2.phar.tar');
$fname3 = $copy->path;
$stub = '<?php echo "first stub\n"; __HALT_COMPILER(); ?>';
$file = $stub;

$files = array();
$files['a'] = 'a';
$files['b'] = 'b';
$files['c'] = 'c';

include 'files/phar_test.inc';

$phar = new Phar($fname);
var_dump($phar->isFileFormat(Phar::TAR));
var_dump($phar->getStub());

$phar = $phar->convertToExecutable(Phar::TAR);
var_dump($phar->isFileFormat(Phar::TAR));
var_dump($phar->getStub());

copy($fname2, $fname3);

$phar = new Phar($fname3);
var_dump($phar->isFileFormat(Phar::TAR));
var_dump($phar->getStub());

?>
--EXPECT--
bool(false)
string(48) "<?php echo "first stub\n"; __HALT_COMPILER(); ?>"
bool(true)
string(60) "<?php // tar-based phar archive stub file
__HALT_COMPILER();"
bool(true)
string(60) "<?php // tar-based phar archive stub file
__HALT_COMPILER();"
