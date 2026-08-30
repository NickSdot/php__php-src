--TEST--
SPL: SplFileObject::setCsvControl basic
--CREDITS--
Erwin Poeze <erwin.poeze at gmail.com>
--FILE--
<?php
$file = TestPath::file();
file_put_contents(
    $file,
    <<<CDATA
    'groene appelen'|10
    'gele bananen'|20
    'rode kersen'|30
    CDATA
);
$s = new SplFileObject($file);
$s->setFlags(SplFileObject::READ_CSV);
$s->setCsvControl('|', '\'', '');
foreach ($s as $row) {
    list($fruit, $quantity) = $row;
    echo "$fruit : $quantity\n";
}
?>
--EXPECTF--
Deprecated: Method SplFileObject::setCsvControl() is deprecated since 8.6 in %s on line %d
groene appelen : 10
gele bananen : 20
rode kersen : 30
