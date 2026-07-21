--TEST--
Dom\HTMLDocument::createProcessingInstruction()
--EXTENSIONS--
dom
--FILE--
<?php
$dom = Dom\HTMLDocument::createEmpty();
try {
    $dom->createProcessingInstruction("?>", "");
} catch (DOMException $e) {
    var_dump($e->getCode());
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $dom->createProcessingInstruction("?>", "?>");
} catch (DOMException $e) {
    var_dump($e->getCode());
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $dom->createProcessingInstruction("target", "?>");
} catch (DOMException $e) {
    var_dump($e->getCode());
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
$dom->appendChild($dom->createProcessingInstruction("foo", ""));
$dom->appendChild($dom->createProcessingInstruction("foo", "bar"));
echo $dom->saveHtml();
?>
--EXPECT--
int(5)
DOMException: Invalid Character Error
int(5)
DOMException: Invalid Character Error
int(5)
DOMException: Invalid character sequence "?>" in processing instruction
<?foo ><?foo bar>
