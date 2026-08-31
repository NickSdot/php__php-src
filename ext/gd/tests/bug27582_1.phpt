--TEST--
Bug #27582 (ImageFillToBorder() on alphablending image looses alpha on fill color)
--EXTENSIONS--
gd
--SKIPIF--
<?php
if (!(imagetypes() & IMG_PNG)) {
    die("skip No PNG support");
}
?>
--FILE--
<?php
$dest = Workspace::file();
$im = ImageCreateTrueColor(10, 10);
imagealphablending($im, true);
imagesavealpha($im, true);
$bordercolor=ImageColorAllocateAlpha($im, 0, 0, 0, 2);
$color = ImageColorAllocateAlpha($im, 0, 0, 0, 1);
ImageFillToBorder($im, 5, 5, $bordercolor, $color);
imagepng($im, $dest->path);

$im2 = imagecreatefrompng($dest->path);
$col = imagecolorat($im2, 5, 5);
$color = imagecolorsforindex($im2, $col);
echo $color['alpha'];
?>
--EXPECT--
1
