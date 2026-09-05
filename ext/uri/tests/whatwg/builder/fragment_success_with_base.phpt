--TEST--
Test Uri\WhatWg\UrlBuilder::setFragment() - success - with base URL
--FILE--
<?php

$base = new Uri\WhatWg\Url('https://example.com/#bar');

$url = new Uri\WhatWg\UrlBuilder()
    ->setFragment('foo')
    ->build($base);

var_dump($url->toAsciiString());
var_dump($url);
var_dump($url->equals(new Uri\WhatWg\Url($url->toAsciiString())));
var_dump($url->equals(new Uri\WhatWg\Url('#foo', $base), Uri\UriComparisonMode::IncludeFragment));

?>
--EXPECTF--
string(24) "https://example.com/#foo"
object(Uri\WhatWg\Url)#%d (%d) {
  ["scheme"]=>
  string(5) "https"
  ["username"]=>
  NULL
  ["password"]=>
  NULL
  ["host"]=>
  string(11) "example.com"
  ["port"]=>
  NULL
  ["path"]=>
  string(1) "/"
  ["query"]=>
  NULL
  ["fragment"]=>
  string(3) "foo"
}
bool(true)
bool(true)
