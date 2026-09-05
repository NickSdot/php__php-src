--TEST--
Test Uri\WhatWg\UrlBuilder::build() - success - clears soft errors from previous build
--FILE--
<?php

$builder = new Uri\WhatWg\UrlBuilder()
    ->setScheme('https')
    ->setHost('example.com')
    ->setFragment("a\tb");

$errors = [];
$builder->build(null, $errors);

$url = $builder
    ->setFragment('ab')
    ->build(null, $errors);

var_dump($url->toAsciiString());
var_dump($url);
var_dump($errors);
var_dump($url->equals(new Uri\WhatWg\Url($url->toAsciiString())));

?>
--EXPECTF--
string(23) "https://example.com/#ab"
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
  string(2) "ab"
}
array(0) {
}
bool(true)
