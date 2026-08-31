--TEST--
Test context bootstrap - strict type declarations are preserved
--FILE--
<?php

declare(strict_types=1);

try {
    strlen(1);
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
TypeError: strlen(): Argument #1 ($string) must be of type string, int given
