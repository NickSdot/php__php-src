--TEST--
Test Uri\WhatWg\UrlBuilder::setPath() - success - question mark in opaque path
--FILE--
<?php

$url = new Uri\WhatWg\UrlBuilder()
    ->setScheme('scheme')
    ->setPath('?foo')
    ->build();

var_dump($url->toAsciiString());
var_dump($url);
var_dump($url->equals(new Uri\WhatWg\Url($url->toAsciiString())));

?>
--EXPECTF--
string(13) "scheme:%3Ffoo"
object(Uri\WhatWg\Url)#%d (%d) {
  ["scheme"]=>
  string(6) "scheme"
  ["username"]=>
  NULL
  ["password"]=>
  NULL
  ["host"]=>
  NULL
  ["port"]=>
  NULL
  ["path"]=>
  string(6) "%3Ffoo"
  ["query"]=>
  NULL
  ["fragment"]=>
  NULL
}
bool(true)
