--TEST--
WeakReference object handlers
--FILE--
<?php
$wr = WeakReference::create(new stdClass);

var_dump($wr->disallow);
var_dump(isset($wr->disallow));
unset($wr->disallow);

try {
    $wr->disallow = "writes";
} catch (Error $ex) {
    echo $ex::class, ': ', $ex->getMessage(), PHP_EOL;
}

try {
    $disallow = &$wr->disallowed;
} catch (Error $ex) {
    echo $ex::class, ': ', $ex->getMessage(), PHP_EOL;
}
?>
--EXPECTF--
Warning: Undefined property: WeakReference::$disallow in %s on line %d
NULL
bool(false)
Error: Cannot create dynamic property WeakReference::$disallow
Error: Cannot create dynamic property WeakReference::$disallowed
