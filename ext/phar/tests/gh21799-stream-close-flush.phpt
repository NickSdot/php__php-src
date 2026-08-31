--TEST--
GH-21799: phar_stream_close propagates phar_stream_flush return value
--EXTENSIONS--
phar
--INI--
phar.readonly=0
phar.require_hash=0
--FILE--
<?php
// Regression baseline: fclose() on a phar file handle succeeds on the
// normal code path. phar_stream_close now returns the phar_stream_flush
// result instead of always returning 0.
$fname = Workspace::file('archive.phar')->path;

$phar = new Phar($fname);
$phar->addFromString('hello.txt', 'hello');
unset($phar);

$fp = fopen('phar://' . $fname . '/hello.txt', 'rb');
$content = fread($fp, 1024);
$result = fclose($fp);

echo $content . "\n";
var_dump($result);
echo "no crash\n";
?>
--EXPECT--
hello
bool(true)
no crash
