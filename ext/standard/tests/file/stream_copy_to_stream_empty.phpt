--TEST--
stream_copy_to_stream() from empty file
--FILE--
<?php
$file = Workspace::file();

file_put_contents($file, "");

$in = fopen($file, 'r');
$out = fopen('php://memory', 'w');
var_dump(stream_copy_to_stream($in, $out));

?>
--EXPECT--
int(0)
