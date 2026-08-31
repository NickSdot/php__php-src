--TEST--
GH-21333 (UAF when unlinking entries during iteration of a compressed phar)
--CREDITS--
YuanchengJiang
--EXTENSIONS--
phar
zlib
--INI--
phar.readonly=0
--FILE--
<?php
$phar = Workspace::file('gh21333.phar');
$phar_path = $phar->path;
$phar = new Phar($phar_path);
$phar->addFromString("file", "initial_content");
$phar->addEmptyDir("dir");

$phar2 = $phar->compress(Phar::GZ);

$tmp_src = Workspace::file();
file_put_contents($tmp_src, str_repeat("A", 100));

foreach ($phar2 as $item) {
    @copy($tmp_src, $item);
    @unlink($item);
}

$garbage = get_defined_vars();

echo "Done\n";
?>
--EXPECT--
Done
