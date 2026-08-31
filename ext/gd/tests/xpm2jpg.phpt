--TEST--
xpm --> jpeg conversion test
--EXTENSIONS--
gd
--SKIPIF--
<?php
    if (!function_exists("imagejpeg")) {
        die("skip jpeg support unavailable");
    }
    if (!function_exists("imagecreatefromxpm")) {
        die("skip xpm read support unavailable");
    }
?>
--FILE--
<?php
    $cwd = __DIR__;
    $output = Workspace::file();

    echo "XPM to JPEG conversion: ";
    echo imagejpeg(imagecreatefromxpm($cwd . "/conv_test.xpm"), $output->path) ? 'ok' : 'failed';
    echo "\n";

?>
--EXPECT--
XPM to JPEG conversion: ok
