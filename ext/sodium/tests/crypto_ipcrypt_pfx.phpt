--TEST--
Check for libsodium ipcrypt prefix-preserving (pfx)
--EXTENSIONS--
sodium
--SKIPIF--
<?php
if (!defined('SODIUM_CRYPTO_IPCRYPT_KEYBYTES')) print "skip libsodium without ipcrypt (requires >= 1.0.21)";
?>
--FILE--
<?php
$key = str_repeat("\x02", SODIUM_CRYPTO_IPCRYPT_PFX_KEYBYTES);

/* PFX encrypt/decrypt roundtrip with IPv4 */
$enc = sodium_crypto_ipcrypt_pfx_encrypt("10.0.0.1", $key);
var_dump(sodium_crypto_ipcrypt_pfx_decrypt($enc, $key));

/* PFX is deterministic */
$enc2 = sodium_crypto_ipcrypt_pfx_encrypt("10.0.0.1", $key);
var_dump($enc === $enc2);

/* PFX: IPv4 encrypted output looks like IPv4 */
var_dump(filter_var($enc, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false);

/* PFX: IPv6 roundtrip */
$enc6 = sodium_crypto_ipcrypt_pfx_encrypt("::1", $key);
var_dump(sodium_crypto_ipcrypt_pfx_decrypt($enc6, $key));

/* PFX: IPv6 encrypted output is valid IPv6 */
var_dump(filter_var($enc6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false);

/* PFX: keygen produces correct length */
$gen_key = sodium_crypto_ipcrypt_pfx_keygen();
var_dump(strlen($gen_key) === SODIUM_CRYPTO_IPCRYPT_PFX_KEYBYTES);

/* PFX error: wrong key length */
try {
    sodium_crypto_ipcrypt_pfx_encrypt("10.0.0.1", "short");
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

/* PFX error: invalid IP */
try {
    sodium_crypto_ipcrypt_pfx_encrypt("not_an_ip", $key);
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

/* PFX error: wrong key length for decrypt */
try {
    sodium_crypto_ipcrypt_pfx_decrypt("10.0.0.1", "short");
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}
?>
--EXPECT--
string(8) "10.0.0.1"
bool(true)
bool(true)
string(3) "::1"
bool(true)
bool(true)
SodiumException: sodium_crypto_ipcrypt_pfx_encrypt(): Argument #2 ($key) must be SODIUM_CRYPTO_IPCRYPT_PFX_KEYBYTES bytes long
SodiumException: sodium_crypto_ipcrypt_pfx_encrypt(): Argument #1 ($ip) must be a valid IP address
SodiumException: sodium_crypto_ipcrypt_pfx_decrypt(): Argument #2 ($key) must be SODIUM_CRYPTO_IPCRYPT_PFX_KEYBYTES bytes long
