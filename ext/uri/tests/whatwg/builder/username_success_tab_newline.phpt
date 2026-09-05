--TEST--
Test Uri\WhatWg\UrlBuilder::setUsername() - success - contains tab and newline characters
--FILE--
<?php

$errors = [];

$url = new Uri\WhatWg\UrlBuilder()
    ->setScheme("\tfo\no")
    ->setHost('example.com')
    ->setUsername("f\no\ro\t")
    ->build(softErrors: $errors);

var_dump($url->toAsciiString());
var_dump($url);
var_dump($errors);
var_dump($url->equals(new Uri\WhatWg\Url($url->toAsciiString())));

?>
--EXPECTF--
string(30) "foo://f%r%%r0Ao%r%%r0Do%r%%r09@example.com"
object(Uri\WhatWg\Url)#%d (8) {
  ["scheme"]=>
  string(3) "foo"
  ["username"]=>
  string(12) "f%r%%r0Ao%r%%r0Do%r%%r09"
  ["password"]=>
  string(0) ""
  ["host"]=>
  string(11) "example.com"
  ["port"]=>
  NULL
  ["path"]=>
  string(0) ""
  ["query"]=>
  NULL
  ["fragment"]=>
  NULL
}
array(1) {
  [0]=>
  object(Uri\WhatWg\UrlValidationError)#%d (3) {
    ["context"]=>
    string(5) "	fo
o"
    ["type"]=>
    enum(Uri\WhatWg\UrlValidationErrorType::InvalidUrlUnit)
    ["failure"]=>
    bool(false)
  }
}
bool(true)
