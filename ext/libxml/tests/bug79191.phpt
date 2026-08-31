--TEST--
Bug #79191 (Error in SoapClient ctor disables DOMDocument::save())
--EXTENSIONS--
soap
dom
--FILE--
<?php
try {
    new \SoapClient('does-not-exist.wsdl');
} catch (Throwable $t) {
}

$dom = new DOMDocument;
$dom->loadxml('<?xml version="1.0" ?><root />');
$file = Workspace::file();
var_dump($dom->save($file));
?>
--EXPECTF--
int(%d)
