--TEST--
Test Uri\WhatWg\UrlBuilder::setUsername() - error - missing opaque host
--FILE--
<?php

$builder = new Uri\WhatWg\UrlBuilder()
    ->setScheme('scheme')
    ->setUsername('user');

try {
    $builder->build();
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
    var_dump($e->errors);
}

?>
--EXPECT--
Uri\WhatWg\InvalidUrlException: The specified URL cannot have username
array(0) {
}
