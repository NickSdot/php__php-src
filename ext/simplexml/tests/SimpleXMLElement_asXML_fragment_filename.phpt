--TEST--
SimpleXMLElement::asXML() with a fragment and a filename
--EXTENSIONS--
simplexml
--FILE--
<?php

$sxe = simplexml_load_string(<<<XML
<?xml version="1.0"?>
<container>
    <container2>
        <child id="foo">bar</child>
    </container2>
</container>
XML);
$file = TestPath::file();
$sxe->container2->asXML($file);

// Note: the strange indent is correct: the indent text node preceding container2 is not emitted.
echo file_get_contents($file);

?>
--EXPECT--
<container2>
        <child id="foo">bar</child>
    </container2>
