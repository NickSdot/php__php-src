--TEST--
Test Uri\WhatWg\UrlBuilder::setPath() - success - colon in first segment with base URL
--XFAIL--
not yet: builder does not resolve this reference against the base URL correctly
--FILE--
<?php

$base = new Uri\WhatWg\Url('https://user:pass@example.com:123/base/path?oldQuery#oldFragment');

$url = new Uri\WhatWg\UrlBuilder()
    ->setPath('new:Path')
    ->build($base);

var_dump($url->toAsciiString());
var_dump($url);
var_dump($url->equals(new Uri\WhatWg\Url($url->toAsciiString())));
var_dump($url->equals(new Uri\WhatWg\Url('./new:Path', $base), Uri\UriComparisonMode::IncludeFragment));

?>
--EXPECTF--
string(47) "https://user:pass@example.com:123/base/new:Path"
object(Uri\WhatWg\Url)#%d (%d) {
  ["scheme"]=>
  string(5) "https"
  ["username"]=>
  string(4) "user"
  ["password"]=>
  string(4) "pass"
  ["host"]=>
  string(11) "example.com"
  ["port"]=>
  int(123)
  ["path"]=>
  string(14) "/base/new:Path"
  ["query"]=>
  NULL
  ["fragment"]=>
  NULL
}
bool(true)
bool(true)
