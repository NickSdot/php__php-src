--TEST--
DOM Document : save and saveXML
--CREDITS--
Sami Greenbury (sami@patabugen.co.uk)
# TestFest 2008
--EXTENSIONS--
dom
--FILE--
<?php

$xml = <<< EOXML
<?xml version="1.0" encoding="utf-8"?>
<courses>
    <!-- Hello World! -->
    <aNode>
        <childNode>
            <childlessNode />
        </childNode>
    </aNode>
</courses>
EOXML;

$dom = new DOMDocument();
$dom->loadXML($xml);
$root = $dom->documentElement;
$filename = Workspace::file();
var_dump($dom->save($filename));
$result = file_get_contents($filename);
var_dump($result == $dom->saveXML());
?>
--EXPECT--
int(181)
bool(true)
