--TEST--
xbm --> png conversion test
--EXTENSIONS--
gd
--SKIPIF--
<?php
    if (!function_exists("imagepng")) {
        die("skip png support unavailable");
    }
    if (!function_exists("imagecreatefromxbm")) {
        die("skip xbm read support unavailable");
    }
?>
--FILE--
<?php
    $cwd = __DIR__;
    $output = TestPath::file();

    echo "XBM to PNG conversion: ";
    echo imagepng(imagecreatefromxbm($cwd . "/conv_test.xbm"), $output->path) ? 'ok' : 'failed';
    echo "\n";

?>
--EXPECT--
XBM to PNG conversion: ok
