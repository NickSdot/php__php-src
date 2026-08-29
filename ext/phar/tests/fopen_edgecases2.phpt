--TEST--
Phar: test edge cases of fopen() function interception #2
--EXTENSIONS--
phar
--INI--
phar.readonly=0
--FILE--
<?php
Phar::interceptFileFuncs();
$fname = TestPath::file('fopen_edgecases2.phar.php')->path;
$sidecar = TestPath::file('fopen_edgecases2.txt');
$pname = 'phar://' . $fname;

try {
    fopen(array(), 'r');
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}
chdir(__DIR__);
file_put_contents($fname, "blah\n");
file_put_contents($sidecar, "test\n");
$a = fopen($fname, 'rb');
echo fread($a, 1000);
fclose($a);
unlink($fname);
mkdir($pname . '/oops');
file_put_contents($pname . '/foo/hi', '<?php
$context = stream_context_create();
$a = fopen($sidecar, "rb", false, $context);
echo fread($a, 1000);
fclose($a);
fopen("../oops", "r");
?>
');
include $pname . '/foo/hi';
?>
--EXPECTF--
TypeError: fopen(): Argument #1 ($filename) must be of type string, array given
blah
test

Warning: fopen(): Failed to open stream: phar error: path "oops" is a directory in phar://%sfopen_edgecases2.phar.php/foo/hi on line %d
