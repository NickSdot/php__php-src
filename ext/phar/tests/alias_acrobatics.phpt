--TEST--
Phar: alias edge cases
--EXTENSIONS--
phar
--INI--
phar.readonly=0
--FILE--
<?php

$fname = TestPath::file('alias_acrobatics.phar')->path;
$fname2 = TestPath::file('alias_acrobatics.2.phar')->path;

$p = new Phar($fname);

$p->setAlias('foo');
$p['unused'] = 'hi';
try {
$a = new Phar($fname2, 0, 'foo');
} catch (Exception $e) {
echo $e::class, ': ', $e->getMessage(), "\n";
}
copy($fname, $fname2);
echo "2\n";
try {
$a = new Phar($fname2);
} catch (Exception $e) {
echo $e::class, ': ', $e->getMessage(), "\n";
}
try {
$b = new Phar($fname, 0, 'another');
} catch (Exception $e) {
echo $e::class, ': ', $e->getMessage(), "\n";
}
?>
--EXPECTF--
UnexpectedValueException: alias "foo" is already used for archive "%salias_acrobatics.phar" cannot be overloaded with "%salias_acrobatics.2.phar"
2
UnexpectedValueException: Cannot open archive "%salias_acrobatics.2.phar", alias is already in use by existing archive
UnexpectedValueException: alias "another" is already used for archive "%salias_acrobatics.phar" cannot be overloaded with "%salias_acrobatics.phar"
