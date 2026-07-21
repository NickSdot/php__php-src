--TEST--
Bug #44648 (Attribute names not checked for well formedness)
--EXTENSIONS--
dom
--FILE--
<?php

$doc = new DOMDocument();
$doc->loadXML('<root/>');

$root = $doc->documentElement;

try {
  $attr = new DOMAttr('@acb', '123');
  $root->setAttributeNode($attr);
} catch (DOMException $e) {
  echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
  $root->setAttribute('@def', '456');
} catch (DOMException $e) {
  echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
  $root->setAttributeNS(NULL, '@ghi', '789');
} catch (DOMException $e) {
  echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
  $root->setAttributeNS('urn::test', 'a:g@hi', '789');
} catch (DOMException $e) {
  echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

echo $doc->saveXML($root);
?>
--EXPECT--
DOMException: Invalid Character Error
DOMException: Invalid Character Error
DOMException: Invalid Character Error
DOMException: Namespace Error
<root/>
