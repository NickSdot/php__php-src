--TEST--
Test Uri\WhatWg\UrlBuilder::setHost() - error - invalid host with base URL containing opaque path
--FILE--
<?php

$base = new Uri\WhatWg\Url('scheme:opaque?oldQuery#oldFragment');

$builder = new Uri\WhatWg\UrlBuilder()
    ->setHost('exa mple');

$referenceFailureType = null;

foreach ([
    fn() => new Uri\WhatWg\Url('//exa mple', $base),
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
    string(2) "//"
    ["type"]=>
    enum(Uri\WhatWg\UrlValidationErrorType::MissingSchemeNonRelativeUrl)
    ["failure"]=>
    bool(true)
  }
}
