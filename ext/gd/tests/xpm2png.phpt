--TEST--
xpm --> png conversion test
--EXTENSIONS--
gd
--SKIPIF--
<?php
    if (!function_exists("imagepng")) {
        die("skip png support unavailable");
    }
    if (!function_exists("imagecreatefromxpm")) {
        die("skip xpm read support unavailable");
    }
?>
--FILE--
<?php
    $cwd = __DIR__;
    $output = Workspace::file();

    echo "XPM to PNG conversion: ";
    echo imagepng(imagecreatefromxpm($cwd . "/conv_test.xpm"), $output->path) ? 'ok' : 'failed';
    echo "\n";

?>
--EXPECT--
XPM to PNG conversion: ok
