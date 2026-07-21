--TEST--
DOMDocumentType::systemId with invalid state.
--CREDITS--
Eric Lee Stewart <ericleestewart@gmail.com>
# TestFest Atlanta 2009-05-25
--EXTENSIONS--
dom
--FILE--
<?php
$doctype = new DOMDocumentType();
try {
    $systemId = $doctype->systemId;
} catch (DOMException $exception) {
    echo $exception::class, ': ', $exception->getMessage(), PHP_EOL;
}
?>
--EXPECT--
DOMException: Invalid State Error
