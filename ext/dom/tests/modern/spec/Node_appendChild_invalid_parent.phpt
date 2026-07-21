--TEST--
Dom\Node::appendChild() invalid parent
--EXTENSIONS--
dom
--FILE--
<?php

$dom = Dom\XMLDocument::createEmpty();

$text = $dom->createTextNode('Hello World');
$cdata = $dom->createCDATASection('Hello World');
$pi = $dom->createProcessingInstruction('Hello', '');
$attr = $dom->createAttribute('Hello');

try {
    $text->appendChild($dom->createElement('br'));
} catch (DOMException $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $cdata->appendChild($dom->createElement('br'));
} catch (DOMException $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $pi->appendChild($dom->createElement('br'));
} catch (DOMException $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $attr->appendChild($dom->createElement('br'));
} catch (DOMException $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
DOMException: Hierarchy Request Error
DOMException: Hierarchy Request Error
DOMException: Hierarchy Request Error
DOMException: Hierarchy Request Error
