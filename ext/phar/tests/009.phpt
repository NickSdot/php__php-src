--TEST--
Phar::mapPhar too many manifest entries
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
--FILE--
<?php
$archive = TestPath::file('009.phar.php');
$file = "<?php
Phar::mapPhar('hio');
__HALT_COMPILER(); ?>";
$file .= pack('VVnVVV', 500, 500, 0x1000, 0x00000000, 0, 0) .  str_repeat('A', 500);
file_put_contents($archive->path, $file);
try {
include $archive->path;
} catch (Exception $e) {
echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECTF--
PharException: internal corruption of phar "%s009.phar.php" (too many manifest entries for size of manifest)
