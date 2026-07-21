--TEST--
WeakReference serials
--FILE--
<?php
$wr = WeakReference::create(new stdClass);

try {
    serialize($wr);
} catch (Exception $ex) {
    echo $ex::class, ': ', $ex->getMessage(), PHP_EOL;
}

$wrs = 'O:13:"WeakReference":0:{}';

try {
	var_dump(unserialize($wrs));
} catch (Exception $ex) {
    echo $ex::class, ': ', $ex->getMessage(), PHP_EOL;
}
?>
--EXPECT--
Exception: Serialization of 'WeakReference' is not allowed
Exception: Unserialization of 'WeakReference' is not allowed
