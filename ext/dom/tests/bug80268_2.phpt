--TEST--
Bug #80268 (loadHTML() truncates at NUL bytes)
--EXTENSIONS--
dom
--SKIPIF--
<?php
if (LIBXML_VERSION < 20912) die('skip For libxml2 >= 2.9.12 only');
?>
--FILE--
<?php
$doc = new DOMDocument;
$doc->loadHTML("<p>foo\0bar</p>", LIBXML_NOERROR);
$html = $doc->saveHTML();
var_dump(strpos($html, '<p>foo</p>') !== false);

$file = TestPath::file();
file_put_contents($file, "<p>foo\0bar</p>");
$doc = new DOMDocument;
$doc->loadHTMLFile($file, LIBXML_NOERROR);
$html = $doc->saveHTML();
var_dump(strpos($html, '<p>foo</p>') !== false);
?>
--EXPECTF--
bool(false)
bool(false)
