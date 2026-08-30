--TEST--
Bug #66660 (Composer.phar install/update fails)
--STDIN--
<?php __CLASS__ ?>
--FILE--
<?php
$file = TestPath::file();
file_put_contents($file, "<?php __CLASS__ ?>");
echo php_strip_whitespace($file);
?>
--EXPECT--
<?php __CLASS__ ?>
