--TEST--
Test strip_tags() - argument errors
--FILE--
<?php

try {
    strip_tags('<p>text</p>', new stdClass());
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
TypeError: strip_tags(): Argument #2 ($allowed_tags) must be of type array|string|null, stdClass given
