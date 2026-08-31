--TEST--
GHSA-3qrf-m4j2-pcrr (libxml global state entity loader bypass)
--EXTENSIONS--
libxml
simplexml
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
simplexml_load_string($xml);
echo "--- Constructor test ---\n";
new SimpleXMLElement($xml);
echo "--- File test ---\n";
$file = Workspace::file();
file_put_contents($file, $xml);
simplexml_load_file($file);

echo "Done\n";

?>
--EXPECT--
--- String test ---
--- Constructor test ---
--- File test ---
Done
