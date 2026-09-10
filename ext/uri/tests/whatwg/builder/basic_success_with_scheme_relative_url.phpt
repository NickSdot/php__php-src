--TEST--
Test Uri\WhatWg\UrlBuilder basic - success - with scheme-relative URL
--FILE--
<?php

$base = new Uri\WhatWg\Url('https://user:pass@example.com:123/foo/bar?query#hash');

$url = new Uri\WhatWg\UrlBuilder()
    ->setHost('example.net')
    ->setPath('/foo/bar/baz')
    ->setPort(124)
    ->build($base);

var_dump($url->toAsciiString());
var_dump($url);
var_dump($url->equals(new Uri\WhatWg\Url($url->toAsciiString())));
var_dump($url->equals(new Uri\WhatWg\Url('//example.net:124/foo/bar/baz', $base), Uri\UriComparisonMode::IncludeFragment));

?>
--EXPECTF--
string(35) "https://example.net:124/foo/bar/baz"
object(Uri\WhatWg\Url)#%d (%d) {
  ["scheme"]=>
  string(5) "https"
  ["username"]=>
  NULL
  ["password"]=>
  NULL
  ["host"]=>
  string(11) "example.net"
  ["port"]=>
  int(124)
  ["path"]=>
  string(12) "/foo/bar/baz"
  ["query"]=>
  NULL
  ["fragment"]=>
  NULL
}
bool(true)
bool(true)
