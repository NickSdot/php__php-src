--TEST--
Test substr_count() function (error conditions)
--FILE--
<?php

echo "\n*** Testing error conditions ***\n";
$str = 'abcdefghik';

/* offset before start */
try {
    substr_count($str, "t", -20);
} catch (ValueError $exception) {
    echo $exception::class, ': ', $exception->getMessage(), PHP_EOL;
}

/* offset > size of the string */
try {
    substr_count($str, "t", 25);
} catch (ValueError $exception) {
    echo $exception::class, ': ', $exception->getMessage(), PHP_EOL;
}

/* Using offset and length to go beyond the size of the string:
   Exception is expected, as length+offset > length of string */
try {
    substr_count($str, "i", 5, 7);
} catch (ValueError $exception) {
    echo $exception::class, ': ', $exception->getMessage(), PHP_EOL;
}

/* length too small */
try {
    substr_count($str, "t", 2, -20);
} catch (ValueError $exception) {
    echo $exception::class, ': ', $exception->getMessage(), PHP_EOL;
}

echo "Done\n";

?>
--EXPECT--
*** Testing error conditions ***
ValueError: substr_count(): Argument #3 ($offset) must be contained in argument #1 ($haystack)
ValueError: substr_count(): Argument #3 ($offset) must be contained in argument #1 ($haystack)
ValueError: substr_count(): Argument #4 ($length) must be contained in argument #1 ($haystack)
ValueError: substr_count(): Argument #4 ($length) must be contained in argument #1 ($haystack)
Done
