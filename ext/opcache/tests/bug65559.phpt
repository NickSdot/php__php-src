--TEST--
Bug #65559 (cache not cleared if changes occur while running)
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.file_update_protection=2
--EXTENSIONS--
opcache
--FILE--
<?php
$file = Workspace::file();
file_put_contents($file, '<?php return 1;');
$var = include $file;
var_dump($var);
file_put_contents($file, '<?php return 2;');
$var = include $file;
var_dump($var);
?>
--EXPECT--
int(1)
int(2)
