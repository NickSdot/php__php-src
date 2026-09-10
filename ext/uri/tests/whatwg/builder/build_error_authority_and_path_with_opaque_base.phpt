--TEST--
Test Uri\WhatWg\UrlBuilder::build() - error - authority and path with base URL containing opaque path
--FILE--
<?php

$base = new Uri\WhatWg\Url('scheme:opaquePath');

$builder = new Uri\WhatWg\UrlBuilder()
    ->setHost('example.net')
    ->setPath('/newPath');

$referenceFailureType = null;

foreach ([
    fn() => new Uri\WhatWg\Url('//example.net/newPath', $base),
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
Uri\WhatWg\InvalidUrlException: The specified URI is malformed (MissingSchemeNonRelativeUrl)
Uri\WhatWg\InvalidUrlException: The specified URI is malformed (MissingSchemeNonRelativeUrl)
bool(true)
array(1) {
  [0]=>
  object(Uri\WhatWg\UrlValidationError)#%d (3) {
    ["context"]=>
    string(10) "///newPath"
    ["type"]=>
    enum(Uri\WhatWg\UrlValidationErrorType::MissingSchemeNonRelativeUrl)
    ["failure"]=>
    bool(true)
  }
}
