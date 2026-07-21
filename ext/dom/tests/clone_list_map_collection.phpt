--TEST--
Cloning node lists, maps, and collections should fail
--EXTENSIONS--
dom
--FILE--
<?php

$dom = new DOMDocument;
$dom->loadXML('<root a="1"><a/></root>');
try {
    clone $dom->documentElement->attributes;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    clone $dom->documentElement->childNodes;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

$dom = Dom\XMLDocument::createFromString('<!DOCTYPE root [<!ENTITY foo "">]><root a="1"><a/></root>');
try {
    clone $dom->documentElement->attributes;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    clone $dom->documentElement->childNodes;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    clone $dom->documentElement->children;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    clone $dom->doctype->entities;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Trying to clone an uncloneable object of class DOMNamedNodeMap
Error: Trying to clone an uncloneable object of class DOMNodeList
Error: Trying to clone an uncloneable object of class Dom\NamedNodeMap
Error: Trying to clone an uncloneable object of class Dom\NodeList
Error: Trying to clone an uncloneable object of class Dom\HTMLCollection
Error: Trying to clone an uncloneable object of class Dom\DtdNamedNodeMap
