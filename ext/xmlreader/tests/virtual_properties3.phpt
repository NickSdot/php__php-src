--TEST--
Virtual property unset tests
--EXTENSIONS--
xmlreader
--FILE--
<?php

class MyXMLReader extends XMLReader
{
    public int $x;
}

$reader = new MyXMLReader();

try {
    unset($reader->attributeCount);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    unset($reader->baseURI);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    unset($reader->depth);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    unset($reader->hasAttributes);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    unset($reader->hasValue);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

unset($reader->x);
var_dump(isset($reader->x));

?>
--EXPECT--
Error: Cannot unset MyXMLReader::$attributeCount
Error: Cannot unset MyXMLReader::$baseURI
Error: Cannot unset MyXMLReader::$depth
Error: Cannot unset MyXMLReader::$hasAttributes
Error: Cannot unset MyXMLReader::$hasValue
bool(false)
