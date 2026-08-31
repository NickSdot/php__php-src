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
$filename = Workspace::file();
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
