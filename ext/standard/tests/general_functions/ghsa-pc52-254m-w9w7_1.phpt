--TEST--
GHSA-54hq-v5wp-fqgv - proc_open does not correctly escape args for bat files
--SKIPIF--
<?php
if( substr(PHP_OS, 0, 3) != "WIN" )
  die('skip Run only on Windows');
?>
--FILE--
<?php

$batch_file_content = <<<EOT
@echo off
powershell -Command "Write-Output '%1%'"
EOT;
$batch_file = TestPath::file('batch.bat');

file_put_contents($batch_file, $batch_file_content);

$descriptorspec = [STDIN, STDOUT, STDOUT];
$proc = proc_open([$batch_file, "\"&notepad.exe"], $descriptorspec, $pipes);
proc_close($proc);

?>
--EXPECT--
"&notepad.exe
