--TEST--
GH-13774 (Restore Warning instead of Fatal Error in gd_webp.c)
--EXTENSIONS--
gd
--SKIPIF--
<?php
$support = gd_info();
if (!isset($support['WebP Support']) || $support['WebP Support'] === false) {
    print 'skip webp support not available';
}
?>
--FILE--
<?php
$empty_webp = Workspace::file();
file_put_contents($empty_webp, "");
$im = imagecreatefromwebp($empty_webp);
var_dump($im);
?>
--EXPECTF--
Warning: imagecreatefromwebp(): gd-webp cannot get webp info in %s on line %d

Warning: imagecreatefromwebp(): "%s" is not a valid WEBP file in %s on line %d
bool(false)
