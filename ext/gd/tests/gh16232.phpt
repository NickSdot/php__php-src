--TEST--
GH-16232 (Overflow on reading wbmp content)
--EXTENSIONS--
gd
--FILE--
<?php
$good_webp = __DIR__ . '/src.wbmp';
$bad_webp = Workspace::file();
copy($good_webp, $bad_webp);
var_dump(imagecreatefromwbmp($bad_webp));
$data = file_get_contents($bad_webp);
$data[3] = chr(255);
file_put_contents($bad_webp, $data);
var_dump(imagecreatefromwbmp($bad_webp));
$data[3] = chr(232);
file_put_contents($bad_webp, $data);
var_dump(imagecreatefromwbmp($bad_webp));
--EXPECTF--
object(GdImage)#3 (0) {
}

Warning: imagecreatefromwbmp(): "%s" is not a valid WBMP file in %s on line %d
bool(false)

Warning: imagecreatefromwbmp(): "%s" is not a valid WBMP file in %s on line %d
bool(false)
