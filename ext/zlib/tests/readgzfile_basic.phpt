--TEST--
Test function readgzfile() reading a gzip relative file
--EXTENSIONS--
zlib
--FILE--
<?php
$plaintxt = <<<EOT
hello world
is a very common test
for all languages

EOT;
$dirname = TestPath::directory('readgzfile_basic_temp');
$filename = $dirname.'/readgzfile_basic.txt.gz';
mkdir($dirname);
$h = gzopen($filename, 'w');
gzwrite($h, $plaintxt);
gzclose($h);


var_dump(readgzfile( $filename ) );
?>
--EXPECT--
hello world
is a very common test
for all languages
int(52)
