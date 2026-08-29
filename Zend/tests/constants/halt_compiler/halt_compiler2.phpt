--TEST--
__HALT_COMPILER(); 2 files
--FILE--
<?php
$text = "<?php echo 'test'; var_dump(__COMPILER_HALT_OFFSET__); __HALT_COMPILER(); ?>
hi there";
file_put_contents(TestPath::file('test1.php')->path, $text);
$text = "<?php echo 'test2'; var_dump(__COMPILER_HALT_OFFSET__); __HALT_COMPILER(); ?>
hi there 2";
file_put_contents(TestPath::file('test2.php')->path, $text);
include TestPath::file('test1.php')->path;
include TestPath::file('test2.php')->path;
?>
--EXPECT--
testint(73)
test2int(74)
