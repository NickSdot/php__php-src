--TEST--
Test Uri\WhatWg\UrlBuilder::build() - error - preserves soft errors argument
--FILE--
<?php

$builder = new Uri\WhatWg\UrlBuilder()
    ->setScheme("ht\ttps")
    ->setHost(null);

$errors = ['unchanged'];

try {
    $builder->build(null, $errors);
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
    var_dump($e->errors);
}

var_dump($errors);

?>
--EXPECTF--
Uri\WhatWg\InvalidUrlException: The specified host is malformed (HostMissing)
array(2) {
  [0]=>
  object(Uri\WhatWg\UrlValidationError)#%d (%d) {
    ["context"]=>
    string(4) "	tps"
    ["type"]=>
    enum(Uri\WhatWg\UrlValidationErrorType::InvalidUrlUnit)
    ["failure"]=>
    bool(false)
  }
  [1]=>
  object(Uri\WhatWg\UrlValidationError)#%d (%d) {
    ["context"]=>
    string(0) ""
    ["type"]=>
    enum(Uri\WhatWg\UrlValidationErrorType::HostMissing)
    ["failure"]=>
    bool(true)
  }
}
array(1) {
  [0]=>
  string(9) "unchanged"
}
