--TEST--
Bug #75776 (Flushing streams with compression filter is broken)
--EXTENSIONS--
zlib
bz2
--FILE--
<?php
$compression = [
    'gz' => ['zlib.deflate', 'gzinflate'],
    'bz2' => ['bzip2.compress', 'bzdecompress']
];
foreach ($compression as $ext => [$filter, $function]) {
    $file = TestPath::file();
    $stream = fopen($file, 'w');
    stream_filter_append($stream, $filter);
    fwrite($stream,"sdfgdfg");
    fflush($stream);
    fclose($stream);

    $compressed = file_get_contents($file);
    var_dump($function($compressed));
}
?>
--EXPECT--
string(7) "sdfgdfg"
string(7) "sdfgdfg"
?>
