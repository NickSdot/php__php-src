--TEST--
Bug #77700 (Writing truecolor images as GIF ignores interlace flag)
--EXTENSIONS--
gd
--FILE--
<?php
$file = TestPath::file();
$im = imagecreatetruecolor(10, 10);
imagefilledrectangle($im, 0, 0, 9, 9, imagecolorallocate($im, 255, 255, 255));
imageinterlace($im, true);
imagegif($im, $file->path);

$im = imagecreatefromgif($file);
var_dump(imageinterlace($im));
?>
--EXPECT--
bool(true)
