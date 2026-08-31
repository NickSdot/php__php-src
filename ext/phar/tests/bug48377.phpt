--TEST--
Phar: PHP bug #48377 "error message unclear on converting phar with existing file"
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
phar.readonly=0
--FILE--
<?php
$archive = Workspace::file('bug48377.phar');
$fname = $archive->path;
$converted = Workspace::file('bug48377.zip');
$fname2 = $converted->path;

touch($fname2);

$phar = new Phar($fname, 0, 'a.phar');
$phar['x'] = 'hi';
try {
    $phar->convertToData(Phar::ZIP, Phar::NONE, 'zip');
} catch (BadMethodCallException $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}
?>
--EXPECTF--
BadMethodCallException: phar "%sbug48377.zip" exists and must be unlinked prior to conversion
