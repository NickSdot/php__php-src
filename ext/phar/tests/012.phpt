--TEST--
Phar::mapPhar valid file
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
--FILE--
<?php

$fname = TestPath::file('012.phar.php')->path;
$pname = 'phar://' . $fname;
$file = "<?php
Phar::mapPhar('hio');
__HALT_COMPILER(); ?>";

$files = array();
$files['a'] = 'a';

include 'files/phar_test.inc';
include $fname;

echo file_get_contents('phar://hio/a');

?>
--EXPECT--
a
