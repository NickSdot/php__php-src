--TEST--
Phar: phar:// require from within
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
--FILE--
<?php
$fname = Workspace::file('archive.phar.php')->path;
$pname = 'phar://' . $fname;
$file = "<?php __HALT_COMPILER(); ?>";

$files = array();
$files['a.php']   = '<?php echo "This is a\n"; require \''.$pname.'/b.php\'; ?>';
$files['b.php']   = '<?php echo "This is b\n"; require \''.$pname.'/b/c.php\'; ?>';
$files['b/c.php'] = '<?php echo "This is b/c\n"; require \''.$pname.'/b/d.php\'; ?>';
$files['b/d.php'] = '<?php echo "This is b/d\n"; require \''.$pname.'/e.php\'; ?>';
$files['e.php']   = '<?php echo "This is e\n"; ?>';

include 'files/phar_test.inc';

require $pname . '/a.php';

?>
--EXPECT--
This is a
This is b
This is b/c
This is b/d
This is e
