--TEST--
gif --> jpeg conversion test
--EXTENSIONS--
gd
--SKIPIF--
<?php
    if (!function_exists("imagejpeg")) {
        die("skip jpeg support unavailable");
    }
    if (!function_exists("imagecreatefromgif")) {
        die("skip gif read support unavailable");
    }
?>
--FILE--
<?php
    $cwd = __DIR__;
    $output = Workspace::file();

    echo "GIF to JPEG conversion: ";
    echo imagejpeg(imagecreatefromgif($cwd . "/conv_test.gif"), $output->path) ? 'ok' : 'failed';
    echo "\n";

?>
--EXPECT--
GIF to JPEG conversion: ok
