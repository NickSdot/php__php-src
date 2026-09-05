--TEST--
Test Uri\WhatWg\UrlBuilder::setQuery() - error - empty string with base URL containing opaque path
--XFAIL--
not yet: empty query with an opaque base URL is accepted
--FILE--
<?php

$base = new Uri\WhatWg\Url('scheme:opaquePath?oldQuery#oldFragment');

$builder = new Uri\WhatWg\UrlBuilder()
    ->setQuery('');

$referenceErrors = null;

foreach ([
    fn() => new Uri\WhatWg\Url('?', $base),
    fn() => $builder->build($base),
] as $build) {
    try {
        $build();
    } catch (Throwable $e) {
        echo $e::class, ': ', $e->getMessage(), "\n";

        if ($referenceErrors === null) {
            $referenceErrors = $e->errors;
            continue;
        }

        var_dump($referenceErrors == $e->errors);
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
    string(1) "?"
    ["type"]=>
    enum(Uri\WhatWg\UrlValidationErrorType::MissingSchemeNonRelativeUrl)
    ["failure"]=>
    bool(true)
  }
}
