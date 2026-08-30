--TEST--
Bug #70102 (imagecreatefromwebm() shifts colors)
--EXTENSIONS--
gd
--SKIPIF--
<?php
if (!GD_BUNDLED && version_compare(GD_VERSION, '2.2.0', '<')) {
    die("skip test requires GD 2.2.0 or higher");
}
if (!function_exists('imagewebp') || !function_exists('imagecreatefromwebp'))
    die('skip WebP support not available');
?>
--FILE--
<?php
$filename = TestPath::file();

$im = imagecreatetruecolor(8, 8);
$white = imagecolorallocate($im, 255, 255, 255);
var_dump($white & 0xffffff);
imagefilledrectangle($im, 0, 0, 7, 7, $white);
imagewebp($im, $filename->path);

$im = imagecreatefromwebp($filename->path);
$color = imagecolorat($im, 4, 4);
var_dump($color & 0xffffff);
?>
--EXPECT--
int(16777215)
int(16777215)
