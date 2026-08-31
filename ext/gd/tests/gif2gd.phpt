--TEST--
gif --> gd1/gd2 conversion test
--EXTENSIONS--
gd
--SKIPIF--
<?php
    if (!GD_BUNDLED && version_compare(GD_VERSION, '2.3.3', '>=')) {
        die("skip test requires GD 2.3.2 or older");
    }

    if (!function_exists("imagecreatefromgif")) {
        die("skip gif read support unavailable");
    }
?>
--FILE--
<?php
    $cwd = __DIR__;
    $gd1 = Workspace::file();
    $gd2 = Workspace::file();

    echo "GIF to GD1 conversion: ";
    echo imagegd(imagecreatefromgif($cwd . "/conv_test.gif"), $gd1->path) ? 'ok' : 'failed';
    echo "\n";

    echo "GIF to GD2 conversion: ";
    echo imagegd2(imagecreatefromgif($cwd . "/conv_test.gif"), $gd2->path) ? 'ok' : 'failed';
    echo "\n";

?>
--EXPECT--
GIF to GD1 conversion: ok
GIF to GD2 conversion: ok
