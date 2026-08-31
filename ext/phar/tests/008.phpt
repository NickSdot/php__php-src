--TEST--
Phar::mapPhar truncated manifest (not enough for manifest length)
--EXTENSIONS--
phar
--FILE--
<?php
$archive = Workspace::file('archive.phar.php');
$file = "<?php
Phar::mapPhar('hio');
__HALT_COMPILER(); ?>";
$file .= pack('V', 500) . 'notenough';
file_put_contents($archive, $file);
try {
include $archive;
} catch (Exception $e) {
echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECTF--
PharException: internal corruption of phar "%s" (truncated manifest header)
