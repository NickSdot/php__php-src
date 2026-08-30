--TEST--
CLI shell shebang
--SKIPIF--
<?php
include 'skipif.inc';
if (substr(PHP_OS, 0, 3) == 'WIN') {
    die ("skip not for Windows");
}

if (str_contains(getenv('TEST_PHP_EXECUTABLE'), " ")) {
    die("skip shebang cannot have spaces in its path");
}

if (strlen("#!".getenv('TEST_PHP_EXECUTABLE')) > 127) {
        die ("skip shebang is too long, see http://www.in-ulm.de/~mascheck/various/shebang/#results");
}
?>
--FILE--
<?php
TestContext::useRelativeWorkspace();

$php = getenv('TEST_PHP_EXECUTABLE');

$filename = TestPath::file('021.tmp.php')->path;

$script = "#!$php -n\n".
          "ola\n".
          "<?php echo 1+1,'\n';\n".
          "?>\n".
          "adeus\n";

file_put_contents($filename, $script);
chmod($filename, 0777);

echo shell_exec($filename);

echo "\nDone\n";
?>
--EXPECT--
ola
2
adeus

Done
