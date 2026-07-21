--TEST--
Document::$implementation createDocumentType errors
--EXTENSIONS--
dom
--FILE--
<?php

$dom = Dom\XMLDocument::createEmpty();
try {
    $dom->implementation->createDocumentType("invalid name", "public", "system");
} catch (DOMException $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $dom->implementation->createDocumentType("", "public", "system");
} catch (DOMException $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $dom->implementation->createDocumentType("@", "", "");
} catch (DOMException $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
DOMException: Namespace Error
DOMException: Namespace Error
DOMException: Namespace Error
