--TEST--
Bug #61964 (finfo_open with directory cause invalid free)
--EXTENSIONS--
fileinfo
--FILE--
<?php

$magic_file = __DIR__ . DIRECTORY_SEPARATOR . 'magic私はガラスを食べられます';

$ret = @finfo_open(FILEINFO_NONE, $magic_file . ".non-exits私はガラスを食べられます");
var_dump($ret);

$dir = TestPath::directory('bug61964-mb')->create();

$magic_file_copy = $dir->file();
$magic_file_copy2 = $dir->file();
copy($magic_file, $magic_file_copy);
copy($magic_file, $magic_file_copy2);

$ret = finfo_open(FILEINFO_NONE, $dir);
var_dump($ret);

$ret = @finfo_open(FILEINFO_NONE, $dir);
var_dump($ret);

$ret = @finfo_open(FILEINFO_NONE, $dir->directory('non-exits-dir私はガラスを食べられます'));
var_dump($ret);

// write some test files to test folder
$test1 = $dir->file();
$test2 = $dir->file();
file_put_contents($test1, "string\n> Core\n> Me");
file_put_contents($test2, "a\nb\n");
@mkdir($dir->directory());

finfo_open(FILEINFO_NONE, $dir);
echo "DONE: testing dir with files\n";

?>
--EXPECTF--
bool(false)%A
object(finfo)#%d (0) {
}
object(finfo)#%d (0) {
}
bool(false)%A
Warning: finfo_open(): offset `string' invalid in %sbug61964-mb.php on line %d

Warning: finfo_open(): offset ` Core' invalid in %sbug61964-mb.php on line %d

Warning: finfo_open(): offset ` Me' invalid in %sbug61964-mb.php on line %d

Warning: finfo_open(): offset `a' invalid in %sbug61964-mb.php on line %d

Warning: finfo_open(): offset `b' invalid in %sbug61964-mb.php on line %d

Warning: finfo_open(): Failed to load magic database at "%sbug61964-mb" in %sbug61964-mb.php on line %d
DONE: testing dir with files
