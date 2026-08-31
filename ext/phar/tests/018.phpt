--TEST--
Phar: opendir test, root directory
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
--FILE--
<?php
$fname = Workspace::file('archive.phar.php');
$pname = 'phar://' . $fname;
$file = "<?php
Phar::mapPhar('hio');
__HALT_COMPILER(); ?>";

$files = array();
$files['a'] = 'a';
$files['b/a'] = 'b';
include 'files/phar_test.inc';

include $fname;
$dir = opendir('phar://hio/');
while (false !== ($a = readdir($dir))) {
    var_dump($a);
    var_dump(is_dir('phar://hio/' . $a));
}
?>
--EXPECT--
string(1) "a"
bool(false)
string(1) "b"
bool(true)
