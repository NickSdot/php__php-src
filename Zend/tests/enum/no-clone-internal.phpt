--TEST--
Enum disallows cloning (internal enum)
--EXTENSIONS--
zend_test
--FILE--
<?php

try {
    var_dump(clone ZendTestIntEnum::Foo);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Trying to clone an uncloneable object of class ZendTestIntEnum
