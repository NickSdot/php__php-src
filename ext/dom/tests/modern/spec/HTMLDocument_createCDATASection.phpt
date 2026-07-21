--TEST--
Dom\HTMLDocument::createCDATASection()
--EXTENSIONS--
dom
--FILE--
<?php
$dom = Dom\HTMLDocument::createEmpty();
try {
    $dom->createCDATASection("foo");
} catch (DOMException $e) {
    var_dump($e->getCode());
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $dom->createCDATASection("]]>");
} catch (DOMException $e) {
    var_dump($e->getCode());
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

$dom = Dom\XMLDocument::createEmpty();
try {
    $dom->createCDATASection("]]>");
} catch (DOMException $e) {
    var_dump($e->getCode());
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
$dom->createCDATASection("]>");
?>
--EXPECT--
int(9)
DOMException: This operation is not supported for HTML documents
int(9)
DOMException: This operation is not supported for HTML documents
int(5)
DOMException: Invalid character sequence "]]>" in CDATA section
