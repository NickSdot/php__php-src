--TEST--
Phar: PHP bug #48377 "error message unclear on converting phar with existing file" test #2
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
phar.readonly=0
--FILE--
<?php
$archive = Workspace::file('bug48377.2.zip');
$fname = $archive->path;

$phar = new PharData($fname);
$phar['x'] = 'hi';
try {
    $phar->convertToData(Phar::ZIP, Phar::NONE, 'phar.zip');
} catch (BadMethodCallException $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}
?>
--EXPECTF--
BadMethodCallException: data phar "%sbug48377.2.phar.zip" has invalid extension phar.zip
