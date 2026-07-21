--TEST--
Check that arguments are freed when calling a deprecated function
--EXTENSIONS--
zend_test
--FILE--
<?php

set_error_handler(function($code, $msg) {
    throw new Error($msg);
});

try {
    zend_test_deprecated(new stdClass);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

$ret = new stdClass;
try {
    $ret = zend_test_deprecated(new stdClass());
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $fn = 'zend_test_deprecated';
    $fn(new stdClass);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

$ret = new stdClass;
try {
    $fn = 'zend_test_deprecated';
    $ret = $fn(new stdClass);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Function zend_test_deprecated() is deprecated
Error: Function zend_test_deprecated() is deprecated
Error: Function zend_test_deprecated() is deprecated
Error: Function zend_test_deprecated() is deprecated
