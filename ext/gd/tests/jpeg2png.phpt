--TEST--
jpeg <--> png conversion test
--EXTENSIONS--
gd
--SKIPIF--
<?php
    if (!function_exists("imagecreatefrompng") || !function_exists("imagepng")) {
        die("skip png support unavailable");
    }
    if (!function_exists("imagecreatefromjpeg") || !function_exists("imagejpeg")) {
        die("skip jpeg support unavailable");
    }
?>
--FILE--
<?php
    $cwd = __DIR__;
    $jpeg = TestPath::file();
    $jpng = TestPath::file();
    $png = TestPath::file();
    $pjpeg = TestPath::file();

    echo "PNG to JPEG conversion: ";
    echo imagejpeg(imagecreatefrompng($cwd . "/conv_test.png"), $jpeg->path) ? 'ok' : 'failed';
    echo "\n";

    echo "Generated JPEG to PNG conversion: ";
    echo imagepng(imagecreatefromjpeg($jpeg), $jpng->path) ? 'ok' : 'failed';
    echo "\n";

    echo "JPEG to PNG conversion: ";
    echo imagepng(imagecreatefromjpeg($cwd . "/conv_test.jpg"), $png->path) ? 'ok' : 'failed';
    echo "\n";

    echo "Generated PNG to JPEG conversion: ";
    echo imagejpeg(imagecreatefrompng($png), $pjpeg->path) ? 'ok' : 'failed';
    echo "\n";

?>
--EXPECT--
PNG to JPEG conversion: ok
Generated JPEG to PNG conversion: ok
JPEG to PNG conversion: ok
Generated PNG to JPEG conversion: ok
