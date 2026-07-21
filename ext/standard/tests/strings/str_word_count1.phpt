--TEST--
str_word_count() and invalid arguments
--FILE--
<?php

var_dump(str_word_count(""));

try {
    var_dump(str_word_count("", -1));
} catch (\ValueError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    var_dump(str_word_count("", -1, $a));
} catch (\ValueError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

var_dump($a);
?>
--EXPECTF--
int(0)
ValueError: str_word_count(): Argument #2 ($format) must be a valid format value

Warning: Undefined variable $a in %s on line %d
ValueError: str_word_count(): Argument #2 ($format) must be a valid format value

Warning: Undefined variable $a in %s on line %d
NULL
