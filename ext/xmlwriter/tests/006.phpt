--TEST--
XMLWriter: libxml2 XML Writer, startDTD/writeElementNS
--EXTENSIONS--
xmlwriter
--FILE--
<?php

$doc_dest = Workspace::file();
$xw = xmlwriter_open_uri($doc_dest);
xmlwriter_start_dtd($xw, 'foo', NULL, 'urn:bar');
xmlwriter_end_dtd($xw);
xmlwriter_start_element($xw, 'foo');
xmlwriter_write_element_ns($xw, 'foo', 'bar', 'urn:foo', 'dummy content');
xmlwriter_end_element($xw);

// Force to write and empty the buffer
$output_bytes = xmlwriter_flush($xw, true);
echo file_get_contents($doc_dest);
unset($xw);
?>
--EXPECT--
<!DOCTYPE foo SYSTEM "urn:bar"><foo><foo:bar xmlns:foo="urn:foo">dummy content</foo:bar></foo>
