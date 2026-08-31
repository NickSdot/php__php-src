--TEST--
Bug #66339 (PHP segfaults in imagexbm)
--EXTENSIONS--
gd
--FILE--
<?php
$filename = Workspace::file('bug66339.xbm');
$im = imagecreate(8, 8);
imagecolorallocate($im, 0, 0, 0); // background
$white = imagecolorallocate($im, 255, 255, 255);
imagefilledrectangle($im, 2, 2, 6, 6, $white);
imagexbm($im, NULL);
echo "------------\n";
imagexbm($im, $filename->path);
echo file_get_contents($filename);
?>
--EXPECT--
#define image_width 8
#define image_height 8
static unsigned char image_bits[] = {
  0xFF, 0xFF, 0x83, 0x83, 0x83, 0x83, 0x83, 0xFF};
------------
#define bug66339_width 8
#define bug66339_height 8
static unsigned char bug66339_bits[] = {
  0xFF, 0xFF, 0x83, 0x83, 0x83, 0x83, 0x83, 0xFF};
