--TEST--
Legacy SKIPIF does not expose the FILE-only TestPath utility
--SKIPIF--
<?php

if (class_exists('TestPath', false)) {
    echo 'TestPath is available in SKIPIF';
} else {
    echo 'nocache';
}

?>
--FILE--
<?php

var_dump(class_exists('TestPath', false));

?>
--EXPECT--
bool(true)
