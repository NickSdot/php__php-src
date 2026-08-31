--TEST--
Phar: ensure unset() works properly on a non-flushed phar archive
--EXTENSIONS--
phar
--INI--
phar.readonly=0
--FILE--
<?php
$fname = Workspace::file('test_unset.phar.php');
$fname2 = Workspace::file('test_unset.2.phar.php');

$phar = new Phar($fname); // no entries, never flushed
$phar->setAlias('first');
$phar->setMetadata('hi');
unset($phar);

$phar = new Phar($fname2);
$phar['b'] = 'whatever'; // flushed
try {
   $phar->setAlias('first');
} catch(Exception $e) {
   echo $e::class, ': ', $e->getMessage(), "\n";
}

$phar = new Phar($fname);
var_dump($phar->getMetadata());
var_dump($phar->getAlias());
var_dump(file_exists($fname));

?>
--EXPECTF--
NULL
string(%d) "%stest_unset.phar.php"
bool(false)
