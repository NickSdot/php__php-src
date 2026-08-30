--TEST--
GH-13037 (PharData incorrectly extracts zip file)
--EXTENSIONS--
phar
--FILE--
<?php
$phar = new PharData(__DIR__ . '/files/gh13037.zip');
$output = TestPath::directory();
mkdir($output->path);
$outputFile = $output->file('test');
$phar->extractTo($output->path);
echo file_get_contents($outputFile);
?>
--EXPECT--
hello
