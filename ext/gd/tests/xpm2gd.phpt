--TEST--
xpm --> gd1/gd2 conversion test
--EXTENSIONS--
gd
--SKIPIF--
<?php
    if (!GD_BUNDLED && version_compare(GD_VERSION, '2.3.3', '>=')) {
        die("skip test requires GD 2.3.2 or older");
    }

    if (!function_exists("imagecreatefromxpm")) {
        die("skip xpm read support unavailable");
    }
?>
--FILE--
<?php
    $cwd = __DIR__;
    $gd1 = Workspace::file();
    $gd2 = Workspace::file();

    echo "XPM to GD1 conversion: ";
    echo imagegd(imagecreatefromxpm($cwd . "/conv_test.xpm"), $gd1->path) ? 'ok' : 'failed';
    echo "\n";

    echo "XPM to GD2 conversion: ";
    echo imagegd2(imagecreatefromxpm($cwd . "/conv_test.xpm"), $gd2->path) ? 'ok' : 'failed';
    echo "\n";

?>
--EXPECT--
XPM to GD1 conversion: ok
XPM to GD2 conversion: ok
