--TEST--
GH-17518 (offset overflow phar extractTo())
--EXTENSIONS--
phar
--INI--
phar.readonly=0
--FILE--
<?php
$fname = TestPath::file('gh17518.phar.php')->path;
$phar = new Phar($fname);
$phar['a'] = 'b';
try {
    $phar->extractTo(__DIR__ . '/gh17518', '');
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}
?>
--EXPECTF--
PharException: phar error: attempted to extract non-existent file or directory "" from phar "%sgh17518.phar.php"
