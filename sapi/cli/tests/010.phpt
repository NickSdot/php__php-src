--TEST--
executing a file with -F
--SKIPIF--
<?php
include "skipif.inc";
if (substr(PHP_OS, 0, 3) == 'WIN') {
    die ("skip not for Windows");
}
?>
--FILE--
<?php
TestContext::useRelativeWorkspace();

$php = getenv('TEST_PHP_EXECUTABLE_ESCAPED');

$filename = TestPath::file("010.test.php")->path;
$filename_escaped = escapeshellarg($filename);
$filename_txt = TestPath::file("010-F.test.txt")->path;
$filename_txt_escaped = escapeshellarg($filename_txt);

$code = '
<?php
var_dump(fread(STDIN, 10));
?>
';

file_put_contents($filename, $code);

$txt = '
test
hello';

file_put_contents($filename_txt, $txt);

var_dump(shell_exec("cat $filename_txt_escaped | $php -n -F $filename_escaped"));

?>
--EXPECT--
string(25) "
string(10) "test
hello"
"
