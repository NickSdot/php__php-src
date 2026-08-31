--TEST--
GH-13037 (PharData incorrectly extracts zip file)
--EXTENSIONS--
phar
--FILE--
<?php
$phar = new PharData(__DIR__ . '/files/gh13037.zip');
$outputFile = Workspace::file('test');
$phar->extractTo($outputFile->directory);
echo file_get_contents($outputFile);
?>
--EXPECT--
hello
