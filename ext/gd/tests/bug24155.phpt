--TEST--
Bug #24155 (gdImageRotate270 rotation problem).
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

    $im = imagecreatetruecolor(30, 50);
    imagefill($im, 0, 0, (16777215 - 255));
    $im = imagerotate($im, 270, 255);
    imagepng($im, $dest->path);

    $im2 = imagecreatefrompng($dest->path);

    // Uniform fill + n x 90degrees rotation , the color value does not change
    $col = imagecolorat($im2, 20, 20);
    // 16777215 - 255 = 16776960
    echo "$col\n";

?>
--EXPECT--
16776960
