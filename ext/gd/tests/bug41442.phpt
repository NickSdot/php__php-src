--TEST--
Bug #41442 (imagegd2() under output control)
--EXTENSIONS--
gd
--SKIPIF--
<?php
    if (!GD_BUNDLED && version_compare(GD_VERSION, '2.3.3', '>=')) {
        die("skip test requires GD 2.3.2 or older");
    }

    if (!function_exists("imagegd2")) {
        die("skip GD2 support unavailable");
    }
?>
--FILE--
<?php

$str = file_get_contents(__DIR__.'/src.gd2');
$res = imagecreatefromstring($str);

/* string */
ob_start();
imagegd2($res);
$str2 = ob_get_clean();
var_dump(imagecreatefromstring($str2));

/* file */
$file = TestPath::file();
$file_path = $file->path;
unset($file);
imagegd2($res, $file_path);
$str2 = file_get_contents($file_path);
var_dump(imagecreatefromstring($str2));

echo "Done\n";
?>
--EXPECT--
object(GdImage)#2 (0) {
}
object(GdImage)#2 (0) {
}
Done
