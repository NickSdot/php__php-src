--TEST--
Phar: test to ensure phar.readonly cannot be circumvented
--EXTENSIONS--
phar
--INI--
phar.readonly=0
--FILE--
<?php
$fname = TestPath::file('archive.phar.php');
$fname2 = TestPath::file();
$a = new Phar($fname);
$a->setStub('<?php
Phar::mapPhar();
$phar = new Phar(__FILE__);
var_dump($phar->isWritable());
try {
$phar["b"] = "should not work!";
} catch (Exception $e) {
echo $e->getMessage(),"\n";
}
__HALT_COMPILER();
?>');
$a['hi'] = 'hi';
unset($a);
copy($fname, $fname2);
Phar::unlinkArchive($fname);
ini_set('phar.readonly', 1);
include $fname2;
?>
--EXPECT--
bool(false)
Write operations disabled by the php.ini setting phar.readonly
