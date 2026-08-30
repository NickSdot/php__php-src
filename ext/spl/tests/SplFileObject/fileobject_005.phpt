--TEST--
SPL: SplFileObject truncate tests
--CREDITS--
Mark Ammann
#Hackday Webtuesday 2008-05-24
--FILE--
<?php

$path = TestPath::file()->create();

$fo = new SplFileObject($path, 'w+', true);
$fo->fwrite("blahlubba");
var_dump($fo->ftruncate(4));

$fo->rewind();
var_dump($fo->fgets());

$fo->rewind();
$fo->fwrite("blahlubba");

?>
--EXPECT--
bool(true)
string(4) "blah"
