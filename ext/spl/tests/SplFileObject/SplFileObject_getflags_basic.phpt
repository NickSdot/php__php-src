--TEST--
SPL: SplFileObject::getFlags basic
--CREDITS--
Ricardo Oedietram <ricardo@odracir.nl>
Erwin Poeze <erwin.poeze@gmail.com>
#PFZ June PHP TestFest 2012
--FILE--
<?php

$file = TestPath::file('SplFileObject_getflags_basic.csv')->path;
file_put_contents($file, 'eerste;tweede;derde');

$fo = new SplFileObject($file);

$fo->setFlags(SplFileObject::DROP_NEW_LINE);
var_dump($fo->getFlags());
?>
--EXPECT--
int(1)
