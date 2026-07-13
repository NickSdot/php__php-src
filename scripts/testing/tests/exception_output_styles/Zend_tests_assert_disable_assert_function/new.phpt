--TEST--
Use disable_functions INI setting to disable assert()
--INI--
zend.assertions=1
disable_functions=assert
--FILE--
<?php
try {
    assert(false && "Is this evaluated?");
} catch (Throwable $e) {
    echo $e::class, ": ", $e->getMessage(), "\n";
}
?>
--EXPECT--
Error: Call to undefined function assert()
