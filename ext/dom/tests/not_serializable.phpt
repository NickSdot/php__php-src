--TEST--
DOM classes are not serializable
--EXTENSIONS--
dom
--FILE--
<?php

$doc = new DOMDocument();
$doc->loadXML('<root><node/></root>');
try {
    serialize($doc);
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

$node = $doc->documentElement;
try {
    serialize($node);
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

$xpath = new DOMXPath($doc);
try {
    serialize($xpath);
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

$ns = $xpath->query('//namespace::*')->item(0);
try {
    serialize($ns);
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

$dom = Dom\XMLDocument::createEmpty();
try {
    serialize($dom);
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    serialize(new Dom\XPath($dom));
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Exception: Serialization of 'DOMDocument' is not allowed, unless serialization methods are implemented in a subclass
Exception: Serialization of 'DOMElement' is not allowed, unless serialization methods are implemented in a subclass
Exception: Serialization of 'DOMXPath' is not allowed
Exception: Serialization of 'DOMNameSpaceNode' is not allowed, unless serialization methods are implemented in a subclass
Exception: Serialization of 'Dom\XMLDocument' is not allowed, unless serialization methods are implemented in a subclass
Exception: Serialization of 'Dom\XPath' is not allowed
