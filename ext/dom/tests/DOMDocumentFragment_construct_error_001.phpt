--TEST--
DOMDocumentFragment::__construct() with too many errors.
--CREDITS--
Eric Lee Stewart <ericleestewart@gmail.com>
# TestFest Atlanta 2009-05-24
--EXTENSIONS--
dom
--FILE--
<?php
try {
    $fragment = new DOMDocumentFragment("root");
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECT--
ArgumentCountError: DOMDocumentFragment::__construct() expects exactly 0 arguments, 1 given
