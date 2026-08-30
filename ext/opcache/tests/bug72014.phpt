--TEST--
Bug #72014 (Including a file with anonymous classes multiple times leads to fatal error)
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.file_update_protection=0
--EXTENSIONS--
opcache
--FILE--
<?php
$file = TestPath::file();
file_put_contents($file, <<<PHP
<?php
\$a = new class() { public \$testvar = "Foo\n"; };
echo \$a->testvar;
PHP
);

include($file);
include($file);
include($file);
?>
--EXPECT--
Foo
Foo
Foo
