--TEST--
gif --> png conversion test
--EXTENSIONS--
gd
--SKIPIF--
<?php
    if (!function_exists("imagepng")) {
        die("skip png support unavailable");
    }
    if (!function_exists("imagecreatefromgif")) {
        die("skip gif read support unavailable");
    }
?>
--FILE--
<?php
    $cwd = __DIR__;
    $output = Workspace::file();

    echo "GIF to PNG conversion: ";
    echo imagepng(imagecreatefromgif($cwd . "/conv_test.gif"), $output->path) ? 'ok' : 'failed';
    echo "\n";

?>
--EXPECT--
GIF to PNG conversion: ok
