--TEST--
Test Uri\WhatWg\UrlBuilder::setPassword() - error - missing host
--FILE--
<?php

$builder = new Uri\WhatWg\UrlBuilder()
    ->setScheme('https')
    ->setPassword('password');

try {
    $builder->build();
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
Uri\WhatWg\InvalidUrlException: The specified host is malformed (HostMissing)
