--TEST--
png <--> gd1/gd2 conversion test
--EXTENSIONS--
gd
--SKIPIF--
<?php
    if (!GD_BUNDLED && version_compare(GD_VERSION, '2.3.3', '>=')) {
        die("skip test requires GD 2.3.2 or older");
    }

    if (!function_exists("imagecreatefrompng") || !function_exists("imagepng")) {
        die("skip png support unavailable");
    }
?>
--FILE--
<?php
    $cwd = __DIR__;
    $gd1 = TestPath::file();
    $gd2 = TestPath::file();
    $png1 = TestPath::file();
    $png2 = TestPath::file();

    echo "PNG to GD1 conversion: ";
    echo imagegd(imagecreatefrompng($cwd . "/conv_test.png"), $gd1->path) ? 'ok' : 'failed';
    echo "\n";

    echo "PNG to GD2 conversion: ";
    echo imagegd2(imagecreatefrompng($cwd . "/conv_test.png"), $gd2->path) ? 'ok' : 'failed';
    echo "\n";

    echo "GD1 to PNG conversion: ";
    echo imagepng(imagecreatefromgd($gd1), $png1->path) ? 'ok' : 'failed';
    echo "\n";

    echo "GD2 to PNG conversion: ";
    echo imagepng(imagecreatefromgd2($gd2), $png2->path) ? 'ok' : 'failed';
    echo "\n";

?>
--EXPECT--
PNG to GD1 conversion: ok
PNG to GD2 conversion: ok
GD1 to PNG conversion: ok
GD2 to PNG conversion: ok
