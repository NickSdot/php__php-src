--TEST--
GH-22360 (convert.base64-encode emits padding on incremental flush)
--FILE--
<?php
$file = TestPath::file();
$fp = fopen($file, 'w');
stream_filter_append($fp, 'convert.base64-encode', STREAM_FILTER_WRITE);

fwrite($fp, "ab");
fflush($fp);
fwrite($fp, "c");
fflush($fp);
fclose($fp);

var_dump(file_get_contents($file));
echo base64_encode("abc"), PHP_EOL;
?>
--EXPECT--
string(4) "YWJj"
YWJj
