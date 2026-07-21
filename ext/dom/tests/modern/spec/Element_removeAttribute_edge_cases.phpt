--TEST--
Element::removeAttribute() edge cases
--EXTENSIONS--
dom
--FILE--
<?php

$dom = Dom\XMLDocument::createFromString('<root a="b"/>');
try {
    $dom->documentElement->removeAttributeNode($dom->createAttribute('test'));
} catch (DOMException $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
DOMException: Not Found Error
