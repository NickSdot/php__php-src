--TEST--
Harden against cmd.exe hijacking
--CONFLICTS--
all
--SKIPIF--
<?php
if (PHP_OS_FAMILY !== "Windows") die("skip only for Windows");
?>
--FILE--
<?php
$cmd_file = TestPath::file('cmd.exe');
chdir($cmd_file->directory);
copy(__DIR__ . "/../helpers/bad_cmd.exe", $cmd_file);
$spec = [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]];
var_dump($proc = proc_open("@echo hello", $spec, $pipes, null));
$read = [$pipes[1], $pipes[2]];
$write = $except = null;
if (($num = stream_select($read, $write, $except, 1000)) === false) {
    echo "stream_select() failed\n";
} elseif ($num > 0) {
    foreach ($read as $stream) {
        fpassthru($stream);
    }
}
?>
--EXPECTF--
resource(%d) of type (process)
hello
