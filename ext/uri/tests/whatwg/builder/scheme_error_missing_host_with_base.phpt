--TEST--
Test Uri\WhatWg\UrlBuilder::setScheme() - error - missing host with base URL
--FILE--
<?php

$base = new Uri\WhatWg\Url('https://example.com/base/path');

$builder = new Uri\WhatWg\UrlBuilder()
    ->setScheme('http');

$referenceFailureType = null;

foreach ([
    fn() => new Uri\WhatWg\Url('http:', $base),
    fn() => $builder->build($base),
] as $build) {
    try {
        $build();
    } catch (Throwable $e) {
        echo $e::class, ': ', $e->getMessage(), "\n";

        if ($referenceFailureType === null) {
            $referenceFailureType = $e->errors[0]->type;
            continue;
        }

        var_dump($referenceFailureType === $e->errors[0]->type);
        var_dump($e->errors);
    }
}

?>
--EXPECTF--
Uri\WhatWg\InvalidUrlException: The specified URI is malformed (HostMissing)
Uri\WhatWg\InvalidUrlException: The specified URI is malformed (HostMissing)
bool(true)
array(1) {
  [0]=>
  object(Uri\WhatWg\UrlValidationError)#%d (3) {
    ["context"]=>
    string(0) ""
    ["type"]=>
    enum(Uri\WhatWg\UrlValidationErrorType::HostMissing)
    ["failure"]=>
    bool(true)
  }
}
