--TEST--
Bug #76296 openssl_pkey_get_public does not respect open_basedir
--EXTENSIONS--
openssl
--FILE--
<?php
$dir = TestPath::directory();
$pem = 'file://' . __DIR__ . '/public.key';
if (!is_dir($dir)) {
    mkdir($dir);
}

ini_set('open_basedir', $dir->path);

var_dump(openssl_pkey_get_public($pem));
?>
--EXPECTF--
Warning: openssl_pkey_get_public(): open_basedir restriction in effect. File(%s) is not within the allowed path(s): (%s) in %s on line %d
bool(false)
