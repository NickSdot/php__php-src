--TEST--
Phar: phar:// include (repeated names)
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
--FILE--
<?php
$fname = TestPath::file('025.phar.php')->path;
$pname = 'phar://' . $fname;
$file = "<?php __HALT_COMPILER(); ?>";

$files = array();
$files['a'] = '<?php echo "This is a\n"; ?>';
$files['b'] = '<?php echo "This is b\n"; ?>';
$files['b/b'] = '<?php echo "This is b/b\n"; ?>';

include 'files/phar_test.inc';

include $pname . '/a';
include $pname . '/b';
include $pname . '/b/b';

?>
--EXPECT--
This is a
This is b
This is b/b
