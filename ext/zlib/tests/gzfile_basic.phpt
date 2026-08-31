--TEST--
Test function gzfile() reading a gzip relative file
--EXTENSIONS--
zlib
--SETUP--
<?php
Workspace::useRelative();
?>
--FILE--
<?php
$plaintxt = <<<EOT
hello world
is a very common test
for all languages
EOT;
$filename = Workspace::file('gzfile_basic.txt.gz')->path;
$h = gzopen($filename, 'w');
gzwrite($h, $plaintxt);
gzclose($h);


var_dump(gzfile( $filename ) );
?>
--EXPECT--
array(3) {
  [0]=>
  string(12) "hello world
"
  [1]=>
  string(22) "is a very common test
"
  [2]=>
  string(17) "for all languages"
}
