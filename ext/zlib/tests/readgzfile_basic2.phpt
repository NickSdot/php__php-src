--TEST--
Test function readgzfile() reading a plain relative file
--EXTENSIONS--
zlib
--FILE--
<?php
$plaintxt = <<<EOT
hello world
is a very common test
for all languages

EOT;
$filename = TestPath::file();
$h = fopen($filename, 'w');
fwrite($h, $plaintxt);
fclose($h);


var_dump(readgzfile( $filename ) );
?>
--EXPECT--
hello world
is a very common test
for all languages
int(52)
