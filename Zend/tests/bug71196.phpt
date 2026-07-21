--TEST--
Bug #71196 (Memory leak with out-of-order live ranges)
--FILE--
<?php
try  {
        $a = "1";
        [1, (y().$a.$a) . ($a.$a)];
} catch (Error $e) {
        echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECT--
Error: Call to undefined function y()
