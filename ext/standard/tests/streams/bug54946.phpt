--TEST--
Bug#54946 stream_get_contents infinite loop
--FILE--
<?php
$filename = TestPath::file();
$stream = fopen($filename, "w"); // w or a
$retval = stream_get_contents($stream, 1, 1);
fclose($stream);
var_dump($retval);
$filename = TestPath::file();

$stream = fopen($filename, "a");
$retval = stream_get_contents($stream, 1, 1);
var_dump($retval);
fclose($stream);
$filename = TestPath::file();

$stream = fopen($filename, "a");
fseek($stream, 1);
$retval = stream_get_contents($stream, 1);
var_dump($retval);
fclose($stream);
?>
--EXPECTF--
Notice: stream_get_contents(): Read of 8192 bytes failed with errno=9 Bad file %r(descriptor|number)%r in %s on line %d
string(0) ""

Notice: stream_get_contents(): Read of 8192 bytes failed with errno=9 Bad file %r(descriptor|number)%r in %s on line %d
string(0) ""

Notice: stream_get_contents(): Read of 8192 bytes failed with errno=9 Bad file %r(descriptor|number)%r in %s on line %d
string(0) ""
