--TEST--
Document::createElement() edge cases
--EXTENSIONS--
dom
--FILE--
<?php

$dom = Dom\HTMLDocument::createEmpty();
try {
    $dom->createElement('');
} catch (DOMException $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $dom->createElement('$');
} catch (DOMException $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
DOMException: Invalid Character Error
DOMException: Invalid Character Error
