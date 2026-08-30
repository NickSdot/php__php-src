--TEST--
Bug #77111 php-win.exe corrupts unicode symbols from cli parameters
--SKIPIF--
<?php
include "skipif.inc";

if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
    die("skip this test is for Windows platforms only");
}

$php = dirname(getenv('TEST_PHP_EXECUTABLE')) . DIRECTORY_SEPARATOR . "php-win.exe";
if (!file_exists($php)) {
    die("skip php-win.exe doesn't exist");
}

?>
--FILE--
<?php

$php = dirname(getenv('TEST_PHP_EXECUTABLE')) . DIRECTORY_SEPARATOR . "php-win.exe";

$out_fl = TestPath::file("argv_bug77111.txt")->path;

$argv_fl = TestPath::file("argv_bug77111_test.php")->path;
file_put_contents($argv_fl, "<?php file_put_contents('$out_fl', implode(' ', array_slice(\$argv, 1))); ?>");

shell_exec("$php -n $argv_fl Ästhetik Æstetik Esthétique Estética Эстетика");
var_dump(file_get_contents($out_fl));

?>
--EXPECT--
string(57) "Ästhetik Æstetik Esthétique Estética Эстетика"
