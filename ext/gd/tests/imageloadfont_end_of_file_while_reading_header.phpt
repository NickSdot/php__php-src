--TEST--
imageloadfont() "End of file while reading header"
--CREDITS--
Ike Devolder <ike.devolder@gmail.com>
User Group: PHP-WVL & PHPGent #PHPTestFest
--EXTENSIONS--
gd
--FILE--
<?php
$filename = Workspace::file();

// End of file while reading header
$bin = "\x41\x41\x41\x41\x00\x00\x00\x00\x00\x00";
$fp = fopen($filename, 'wb');
fwrite($fp, $bin);
fclose($fp);
$font = imageloadfont($filename);

// Error while reading header
$bin = "\xe0\x00\x00\x00\x20\x00\x00\x00\x06\x00\x00\x00\x0a\x00\x00";
$fp = fopen($filename, 'wb');
fwrite($fp, $bin);
fclose($fp);
$font = imageloadfont($filename);
?>
--EXPECTF--
Warning: imageloadfont(): End of file while reading header in %s on line %d

Warning: imageloadfont(): End of file while reading header in %s on line %d
