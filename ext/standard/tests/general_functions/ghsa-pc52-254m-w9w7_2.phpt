--TEST--
GHSA-54hq-v5wp-fqgv - proc_open does not correctly escape args for cmd files
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
$batch_file = TestPath::file('ghsa-54hq-v5wp-fqgv_2.cmd');
$batch_file_path = $batch_file->path;

file_put_contents($batch_file_path, $batch_file_content);

$descriptorspec = [STDIN, STDOUT, STDOUT];
$proc = proc_open([$batch_file_path, "\"&notepad<>^()!.exe"], $descriptorspec, $pipes);
proc_close($proc);

?>
--EXPECT--
"&notepad<>^()!.exe
