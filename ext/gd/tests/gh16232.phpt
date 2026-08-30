--TEST--
GH-16232 (Overflow on reading wbmp content)
--EXTENSIONS--
gd
--FILE--
<?php
$good_webp = __DIR__ . '/src.wbmp';
$bad_webp = TestPath::file();
$bad_webp_path = $bad_webp->path;
unset($bad_webp);
copy($good_webp, $bad_webp_path);
var_dump(imagecreatefromwbmp($bad_webp_path));
$data = file_get_contents($bad_webp_path);
$data[3] = chr(255);
file_put_contents($bad_webp_path, $data);
var_dump(imagecreatefromwbmp($bad_webp_path));
$data[3] = chr(232);
file_put_contents($bad_webp_path, $data);
var_dump(imagecreatefromwbmp($bad_webp_path));
--EXPECTF--
object(GdImage)#1 (0) {
}

Warning: imagecreatefromwbmp(): "%s" is not a valid WBMP file in %s on line %d
bool(false)

Warning: imagecreatefromwbmp(): "%s" is not a valid WBMP file in %s on line %d
bool(false)
