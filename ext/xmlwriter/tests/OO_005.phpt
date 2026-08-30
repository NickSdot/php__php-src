--TEST--
XMLWriter: libxml2 XML Writer, comments
--EXTENSIONS--
xmlwriter
--FILE--
<?php

$doc_dest = TestPath::file();
$xw = new XMLWriter();
$xw->openUri($doc_dest);
$xw->startDocument('1.0', 'UTF-8');
$xw->startElement("tag1");
$xw->startComment();
$xw->text('comment');
$xw->endComment();
$xw->writeComment("comment #2");
$xw->endDocument();

// Force to write and empty the buffer
$output_bytes = $xw->flush(true);
echo file_get_contents($doc_dest);
unset($xw);
?>
--EXPECT--
<?xml version="1.0" encoding="UTF-8"?>
<tag1><!--comment--><!--comment #2--></tag1>
