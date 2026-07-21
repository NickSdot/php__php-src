--TEST--
Assign to $this leaks when $this not defined
--FILE--
<?php

try {
    $this->a = new stdClass;
} catch (Error $e) { echo $e::class, ': ', $e->getMessage(), PHP_EOL; }

?>
--EXPECT--
Error: Using $this when not in object context
