--TEST--
Phar::mapPhar buffer overrun
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
--FILE--
<?php
$file = "<?php
Phar::mapPhar('hio');
__HALT_COMPILER(); ?>";

// this fails because the manifest length does not include the other 10 byte manifest data

$manifest = pack('V', 1) . 'a' . pack('VVVVVV', 0, time(), 0, crc32(''), 0x00000000, 0);
$file .= pack('VVnVV', strlen($manifest), 1, 0x1000, 0x00000000, 3) . 'hio' . pack('V', 0) . $manifest;

$archive = TestPath::file('archive.phar.php');
file_put_contents($archive, $file);
try {
include $archive;
echo file_get_contents('phar://hio/a');
} catch (Exception $e) {
echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECTF--
PharException: internal corruption of phar "%s" (too many manifest entries for size of manifest)
