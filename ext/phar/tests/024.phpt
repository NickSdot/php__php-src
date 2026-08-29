--TEST--
Phar: phar:// include
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
--FILE--
<?php
$fname = TestPath::file('024.phar.php')->path;
$pname = 'phar://' . $fname;
$file = "<?php __HALT_COMPILER(); ?>";

$files = array();
$files['a.php'] = '<?php echo "This is a\n"; ?>';
$files['b.php'] = '<?php echo "This is b\n"; ?>';
$files['b/c.php'] = '<?php echo "This is b/c\n"; ?>';

include 'files/phar_test.inc';

include $pname . '/a.php';
include $pname . '/b.php';
include $pname . '/b/c.php';

?>
--EXPECT--
This is a
This is b
This is b/c
