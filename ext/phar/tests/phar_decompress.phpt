--TEST--
Phar::decompress()
--EXTENSIONS--
phar
zlib
--INI--
phar.require_hash=0
phar.readonly=0
--FILE--
<?php
$archive = TestPath::file('phar_decompress.phar.php');
$fname = $archive->path;
$copy = TestPath::file('phar_decompress2.phar.gz');
$fname2 = $copy->path;
TestPath::file('phar_decompress.zip');
TestPath::file('phar_decompress.hooba.phar');
TestPath::file('phar_decompress.phar');
TestPath::file('phar_decompress.phar.gz');
TestPath::file('phar_decompress2.phar');
$pname = 'phar://' . $fname;
$file = '<?php __HALT_COMPILER(); ?>';

$files = array();
$files['a'] = 'a';
$files['b'] = 'b';
$files['c'] = 'c';

include 'files/phar_test.inc';

$phar = new Phar($fname);

$gz = $phar->compress(Phar::GZ);
copy($gz->getPath(), $fname2);
$a = new Phar($fname2);
var_dump($a->isCompressed());
$unc = $a->compress(Phar::NONE);
echo $unc->getPath() . "\n";
$unc2 = $gz->decompress();
echo $unc2->getPath() . "\n";
$unc3 = $gz->decompress('hooba.phar');
echo $unc3->getPath() . "\n";
$zip = $phar->convertToData(Phar::ZIP);
ini_set('phar.readonly', 1);
try {
    $gz->decompress();
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}
try {
    $zip->decompress();
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}
?>
--EXPECTF--
int(4096)
%sphar_decompress2.phar
%sphar_decompress.phar
%sphar_decompress.hooba.phar
UnexpectedValueException: Cannot decompress phar archive, phar is read-only
UnexpectedValueException: Cannot decompress zip-based archives with whole-archive compression
