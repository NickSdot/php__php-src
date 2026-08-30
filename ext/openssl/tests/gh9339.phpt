--TEST--
GH-9339: oid_file path check warning contains uninitialized path
--EXTENSIONS--
openssl
--FILE--
<?php
$configCode = <<<CONFIG
oid_file = %s
[ req ]
default_bits = 1024
CONFIG;

$configFile = TestPath::file();
file_put_contents($configFile, sprintf($configCode,  __DIR__ . '/' . str_repeat('a', 9000)));
openssl_pkey_new([ 'config' => $configFile ]);
?>
--EXPECTF--

Warning: openssl_pkey_new(): Path for oid_file option must be a valid file path in %s on line %d
