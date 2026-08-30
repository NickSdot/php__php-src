--TEST--
SPL: SplFileObject::getSize
--CREDITS--
H�vard Eide <nucleuz at gmail.com>
#Testfest php.no
--INI--
include_path=.
--FILE--
<?php
$file = TestPath::file('data.txt')->path;
file_put_contents($file, "foobar");

$s = new SplFileObject( $file );
echo $s->getSize();
?>
--EXPECT--
6
