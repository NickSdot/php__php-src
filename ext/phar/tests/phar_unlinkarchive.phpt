--TEST--
Phar::unlinkArchive()
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
phar.readonly=0
--FILE--
<?php

try {
Phar::unlinkArchive("");
} catch (Exception $e) {
echo $e::class, ': ', $e->getMessage(), "\n";
}

$archive = TestPath::file('phar_unlinkarchive.phar');
$fname = $archive->path;
$invalid = TestPath::file('phar_unlinkarchive.phar.tar');
$pdname = $invalid->path;

try {
Phar::unlinkArchive($fname);
} catch (Exception $e) {
echo $e::class, ': ', $e->getMessage(), "\n";
}
file_put_contents($pdname, 'blahblah');
try {
Phar::unlinkArchive($pdname);
} catch (Exception $e) {
echo $e::class, ': ', $e->getMessage(), "\n";
}
try {
    Phar::unlinkArchive(array());
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

$pname = 'phar://' . $fname;
$converted = TestPath::file('phar_unlinkarchive.phar.zip');
$fname2 = $converted->path;
$copy = TestPath::file('phar_unlinkarchive.2.phar.zip');
$fname3 = $copy->path;
$stub = '<?php echo "first stub\n"; __HALT_COMPILER(); ?>';
$file = $stub;

$files = array();
$files['a'] = 'a';
$files['b'] = 'b';
$files['c'] = 'c';

include 'files/phar_test.inc';

$phar = new Phar($fname);
var_dump($phar->isFileFormat(Phar::ZIP));
var_dump($phar->getStub());
try {
Phar::unlinkArchive($fname);
} catch (Exception $e) {
echo $e::class, ': ', $e->getMessage(), "\n";
}
$phar = $phar->convertToExecutable(Phar::ZIP);
var_dump($phar->isFileFormat(Phar::ZIP));
var_dump($phar->getStub());

copy($fname2, $fname3);

$phar = new Phar($fname3);
var_dump($phar->isFileFormat(Phar::ZIP));
var_dump($phar->getStub());

Phar::unlinkArchive($fname);
var_dump(file_exists($fname));
$phar = new Phar($fname);
var_dump(count($phar));
$phar['evil.php'] = '<?php
try {
Phar::unlinkArchive(Phar::running(false));
} catch (Exception $e) {echo $e->getMessage(),"\n";}
var_dump(Phar::running(false));
include Phar::running(true) . "/another.php";
?>';
$phar['another.php'] = "hi\n";
unset($phar);
include $pname . '/evil.php';
?>
--EXPECTF--
PharException: Unknown phar archive ""
PharException: Unknown phar archive "%sphar_unlinkarchive.phar"
PharException: Unknown phar archive "%sphar_unlinkarchive.phar.tar": internal corruption of phar "%sphar_unlinkarchive.phar.tar" (truncated entry)
TypeError: Phar::unlinkArchive(): Argument #1 ($filename) must be of type string, array given
bool(false)
string(48) "<?php echo "first stub\n"; __HALT_COMPILER(); ?>"
PharException: phar archive "%sphar_unlinkarchive.phar" has open file handles or objects.  fclose() all file handles, and unset() all objects prior to calling unlinkArchive()
bool(true)
string(60) "<?php // zip-based phar archive stub file
__HALT_COMPILER();"
bool(true)
string(60) "<?php // zip-based phar archive stub file
__HALT_COMPILER();"
bool(false)
int(0)
phar archive "%sphar_unlinkarchive.phar" cannot be unlinked from within itself
string(%d) "%sphar_unlinkarchive.phar"
hi
