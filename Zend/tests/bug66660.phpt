--TEST--
Bug #66660 (Composer.phar install/update fails)
--STDIN--
<?php __CLASS__ ?>
--FILE--
<?php
file_put_contents(TestPath::file('bug66660.tmp.php')->path, "<?php __CLASS__ ?>");
echo php_strip_whitespace(TestPath::file('bug66660.tmp.php')->path);
?>
--EXPECT--
<?php __CLASS__ ?>
