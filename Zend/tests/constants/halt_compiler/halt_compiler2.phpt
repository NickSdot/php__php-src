--TEST--
__HALT_COMPILER(); 2 files
--FILE--
<?php
$file1 = TestPath::file('test1.php');
$file2 = TestPath::file('test2.php');
$text = "<?php echo 'test'; var_dump(__COMPILER_HALT_OFFSET__); __HALT_COMPILER(); ?>
hi there";
file_put_contents($file1, $text);
$text = "<?php echo 'test2'; var_dump(__COMPILER_HALT_OFFSET__); __HALT_COMPILER(); ?>
hi there 2";
file_put_contents($file2, $text);
include $file1->path;
include $file2->path;
?>
--EXPECT--
testint(73)
test2int(74)
