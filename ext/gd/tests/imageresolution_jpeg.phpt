--TEST--
Set and get image resolution of JPEG images
--EXTENSIONS--
gd
--SKIPIF--
<?php
if (!(imagetypes() & IMG_JPEG)) die('skip JPEG support not available');
?>
--FILE--
<?php
$filename = Workspace::file();

$exp = imagecreate(100, 100);
imagecolorallocate($exp, 255, 0, 0);

imageresolution($exp, 71);
imagejpeg($exp, $filename->path);
$act = imagecreatefromjpeg($filename);
var_dump(imageresolution($act));

imageresolution($exp, 71, 299);
imagejpeg($exp, $filename->path);
$act = imagecreatefromjpeg($filename);
var_dump(imageresolution($act));
imageresolution($exp, 71, 299);

try {
	imagejpeg($exp, $filename->path, 101);
} catch (\ValueError $e) {
	echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECT--
array(2) {
  [0]=>
  int(71)
  [1]=>
  int(71)
}
array(2) {
  [0]=>
  int(71)
  [1]=>
  int(299)
}
ValueError: imagejpeg(): Argument #3 ($quality) must be at between -1 and 100
