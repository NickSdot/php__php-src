--TEST--
Context bootstrap preserves strict type declarations in PHPT sections
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
