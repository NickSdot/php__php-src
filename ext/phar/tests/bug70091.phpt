--TEST--
Bug #70091 (Phar does not mark UTF-8 filenames in ZIP archives)
--EXTENSIONS--
phar
zlib
--FILE--
<?php
$archive = TestPath::file('bug70091.zip');
$phar = new PharData($archive->path);
$phar->addFromString('föö', '');
$phar->addFromString('foo', '');
unset($phar);

$stream = fopen($archive->path, 'r');

$data = fread($stream, 8);
var_dump(unpack('H8sig/@6/nflags', $data));

fseek($stream, 53);
$data = fread($stream, 8);
var_dump(unpack('H8sig/@6/nflags', $data));

fseek($stream, 104);
$data = fread($stream, 10);
var_dump(unpack('H8sig/@8/nflags', $data));

fseek($stream, 173);
$data = fread($stream, 10);
var_dump(unpack('H8sig/@8/nflags', $data));
?>
--EXPECT--
array(2) {
  ["sig"]=>
  string(8) "504b0304"
  ["flags"]=>
  int(8)
}
array(2) {
  ["sig"]=>
  string(8) "504b0304"
  ["flags"]=>
  int(8)
}
array(2) {
  ["sig"]=>
  string(8) "504b0102"
  ["flags"]=>
  int(8)
}
array(2) {
  ["sig"]=>
  string(8) "504b0102"
  ["flags"]=>
  int(8)
}
