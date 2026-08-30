--TEST--
Bug #48732 (TTF Bounding box wrong for letters below baseline)
--EXTENSIONS--
gd
--SKIPIF--
<?php
    if(!function_exists('imagefttext')) die('skip imagefttext() not available');
    if (substr(PHP_OS, 0, 3) == 'WIN') die('skip UTF-8 font file names not yet supported on Windows');
    if (!(imagetypes() & IMG_PNG)) {
        die("skip No PNG support");
    }
?>
--FILE--
<?php
$cwd = __DIR__;
$font = "$cwd/Tuffy私はガラスを食べられます.ttf";
$output = TestPath::file();
$g = imagecreate(100, 50);
$bgnd  = imagecolorallocate($g, 255, 255, 255);
$black = imagecolorallocate($g, 0, 0, 0);
$bbox  = imagettftext($g, 12, 0, 0, 20, $black, $font, "ABCEDFGHIJKLMN\nopqrstu\n");
imagepng($g, $output->path);
echo 'Left Bottom: (' . $bbox[0]  . ', ' . $bbox[1] . ')';
?>
--EXPECT--
Left Bottom: (0, 40)
