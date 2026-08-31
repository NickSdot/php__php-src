--TEST--
Phar convert logic leaks metadata
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
phar.readonly=0
--FILE--
<?php
$fname = Workspace::file('phar_convert_metadata_leak.zip')->path;

$phar = new PharData($fname);
$phar->setMetadata("foobar");
$phar['x'] = 'hi';
try {
    $phar->convertToData(Phar::ZIP, Phar::NONE, 'phar.zip');
} catch (BadMethodCallException $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}
?>
--EXPECTF--
BadMethodCallException: data phar "%s" has invalid extension phar.zip
