--TEST--
Test fputcsv() : usage variations - with default enclosure & delimiter of two chars
--FILE--
<?php

/* Testing fputcsv() to write to a file when default enclosure value and delimiter
   of two chars is provided */

echo "*** Testing fputcsv() : with default enclosure & delimiter of two chars ***\n";

$file = TestPath::file();
$fo = new SplFileObject($file, 'w');

try {
    var_dump($fo->fputcsv(array('water', 'fruit'), ',,', '"'));
} catch (ValueError $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

unset($fo);

echo "Done\n";
?>
--EXPECTF--
*** Testing fputcsv() : with default enclosure & delimiter of two chars ***

Deprecated: Method SplFileObject::fputcsv() is deprecated since 8.6 in %s on line %d
ValueError: SplFileObject::fputcsv(): Argument #2 ($separator) must be a single character
Done
