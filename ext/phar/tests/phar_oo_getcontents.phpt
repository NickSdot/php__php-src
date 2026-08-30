--TEST--
Phar object: getContent()
--EXTENSIONS--
phar
--INI--
phar.readonly=0
--FILE--
<?php
$archive = TestPath::file('phar_oo_getcontents.phar.php');
$fname = $archive->path;

$phar = new Phar($fname);
$phar['a/b'] = 'file contents
this works';
$phar->addEmptyDir('hi');
echo $phar['a/b']->getContent() . "\n";
try {
echo $phar['a']->getContent(), "\n";
} catch (Exception $e) {
echo $e::class, ': ', $e->getMessage(), "\n";
}
try {
echo $phar['hi']->getContent(), "\n";
} catch (Exception $e) {
echo $e::class, ': ', $e->getMessage(), "\n";
}
?>
--EXPECTF--
file contents
this works
BadMethodCallException: phar error: Cannot retrieve contents, "a" in phar "%sphar_oo_getcontents.phar.php" is a directory
BadMethodCallException: phar error: Cannot retrieve contents, "hi" in phar "%sphar_oo_getcontents.phar.php" is a directory
