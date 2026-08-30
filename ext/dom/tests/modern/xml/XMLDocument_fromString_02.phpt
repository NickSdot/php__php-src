--TEST--
Dom\XMLDocument::createFromString 02
--EXTENSIONS--
dom
--FILE--
<?php

$dom = Dom\XMLDocument::createFromString('<?xml version="1.0"?><container/>');
$file = TestPath::file();
var_dump($dom->saveXmlFile($file));
echo file_get_contents($file);

?>
--EXPECT--
int(51)
<?xml version="1.0" encoding="UTF-8"?>
<container/>
