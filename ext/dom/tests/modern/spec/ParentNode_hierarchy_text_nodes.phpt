--TEST--
ParentNode hierarchy exceptions with temporary and non-temporary text nodes
--EXTENSIONS--
dom
--FILE--
<?php

$dom = Dom\HTMLDocument::createEmpty();

try {
    $dom->append("bar");
} catch (DOMException $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $dom->append($dom->createTextNode("bar"));
} catch (DOMException $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

$text = $dom->createTextNode("bar");
try {
    $dom->append($text);
} catch (DOMException $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

var_dump($text->parentNode);
var_dump($text->textContent);

$element = $dom->createElement("container");
$text = $element->appendChild($dom->createTextNode("text"));
try {
    $dom->append($text);
} catch (DOMException $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
DOMException: Cannot insert text as a child of a document
DOMException: Cannot insert text as a child of a document
DOMException: Cannot insert text as a child of a document
NULL
string(3) "bar"
DOMException: Cannot insert text as a child of a document
