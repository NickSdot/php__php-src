--TEST--
SPL: SplFileObject::getFlags error 001
--CREDITS--
Ricardo Oedietram <ricardo@odracir.nl>
Erwin Poeze <erwin.poeze@gmail.com>
#PFZ June PHP TestFest 2012
--FILE--
<?php

$file = TestPath::file('SplFileObject_getflags_error001.csv');
file_put_contents($file->path, 'eerste;tweede;derde');


$fo = new SplFileObject($file->path);
$fo->setFlags(SplFileObject::READ_CSV);

$fo->setFlags(SplFileObject::DROP_NEW_LINE);

var_dump($fo->getFlags());

?>
--EXPECT--
int(1)
