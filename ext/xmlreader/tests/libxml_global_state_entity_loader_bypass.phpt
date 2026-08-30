--TEST--
GHSA-3qrf-m4j2-pcrr (libxml global state entity loader bypass)
--EXTENSIONS--
libxml
xmlreader
zend_test
--SKIPIF--
<?php
if (!function_exists('zend_test_override_libxml_global_state')) die('skip not for Windows');
?>
--FILE--
<?php

$xml = "<?xml version='1.0'?><!DOCTYPE root [<!ENTITY % bork SYSTEM \"php://nope\"> %bork;]><nothing/>";

libxml_use_internal_errors(true);
zend_test_override_libxml_global_state();

echo "--- String test ---\n";
$reader = XMLReader::xml($xml);
$reader->read();

echo "--- File test ---\n";
$file = TestPath::file();
file_put_contents($file, $xml);
$reader = XMLReader::open($file);
$reader->read();

echo "--- Stream test ---\n";
$stream = fopen($file, "r");
$reader = XMLReader::fromStream($stream);
$reader->read();
fclose($stream);

echo "Done\n";

?>
--EXPECT--
--- String test ---
--- File test ---
--- Stream test ---
Done
