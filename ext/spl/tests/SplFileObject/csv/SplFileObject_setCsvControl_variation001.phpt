--TEST--
SPL: SplFileObject::setCsvControl variation 001
--CREDITS--
Erwin Poeze <erwin.poeze at gmail.com>
--FILE--
<?php
$file = TestPath::file('csv_control_data_variation001.csv')->path;
file_put_contents($file,
<<<CDATA
"groene appelen",10
"gele bananen",20
"rode kersen",30
CDATA
);
$s = new SplFileObject($file);
$s->setFlags(SplFileObject::READ_CSV);
$s->setCsvControl();
foreach ($s as $row) {
    list($fruit, $quantity) = $row;
    echo "$fruit : $quantity\n";
}
?>
--EXPECTF--
Deprecated: Method SplFileObject::setCsvControl() is deprecated since 8.6 in %s on line %d

Deprecated: SplFileObject::setCsvControl(): the $escape parameter must be provided as its default value will change in %s on line %d
groene appelen : 10
gele bananen : 20
rode kersen : 30
