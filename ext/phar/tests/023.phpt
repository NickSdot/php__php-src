--TEST--
Phar: phar:// file_get_contents
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
--FILE--
<?php
$fname = TestPath::file('023.phar.php')->path;
$pname = 'phar://' . $fname;
$file = "<?php __HALT_COMPILER(); ?>";

$files = array();
$files['a.php'] = '<?php echo "This is a\n"; ?>';
$files['b.php'] = '<?php echo "This is b\n"; ?>';
$files['b/c.php'] = '<?php echo "This is b/c\n"; ?>';

include 'files/phar_test.inc';

var_dump(file_get_contents($pname . '/a.php'));
var_dump(file_get_contents($pname . '/b.php'));
var_dump(file_get_contents($pname . '/b/c.php'));

?>
--EXPECT--
string(28) "<?php echo "This is a\n"; ?>"
string(28) "<?php echo "This is b\n"; ?>"
string(30) "<?php echo "This is b/c\n"; ?>"
