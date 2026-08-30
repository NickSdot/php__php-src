--TEST--
SPL: SplFileObject::setCsvControl error 002
--CREDITS--
Erwin Poeze <erwin.poeze at gmail.com>
--FILE--
<?php
$file = TestPath::file();
file_put_contents($file,
<<<CDATA
'groene appelen'|10
'gele bananen'|20
'rode kersen'|30
CDATA
);
$s = new SplFileObject($file);
$s->setFlags(SplFileObject::READ_CSV);
try {
    $s->setCsvControl('|', 'two');
} catch (ValueError $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}
?>
--EXPECTF--
Deprecated: Method SplFileObject::setCsvControl() is deprecated since 8.6 in %s on line %d
ValueError: SplFileObject::setCsvControl(): Argument #2 ($enclosure) must be a single character
