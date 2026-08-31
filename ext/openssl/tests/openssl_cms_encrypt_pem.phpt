--TEST--
openssl_cms_encrypt() pem test
--EXTENSIONS--
openssl
--FILE--
<?php
$infile = __DIR__ . "/plain.txt";
$cryptfile = Workspace::file();
$decryptfile = Workspace::file();
$single_cert = "file://" . __DIR__ . "/cert.crt";
$privkey = "file://" . __DIR__ . "/private_rsa_1024.key";
$headers = array("test@test", "testing openssl_cms_encrypt()");
$cipher = OPENSSL_CIPHER_AES_128_CBC;

var_dump(openssl_cms_encrypt($infile, $cryptfile, $single_cert, $headers, OPENSSL_CMS_BINARY, OPENSSL_ENCODING_PEM, $cipher));
if (openssl_cms_decrypt($cryptfile, $decryptfile, $single_cert, $privkey, OPENSSL_ENCODING_PEM) == false) {
    print "PEM decrypt error\n";
    print "recipient:\n";
    readfile($single_cert);
    print "input:\n";
    readfile($infile);
    print "outfile:\n";
    readfile($cryptfile);
    while (( $errstr=openssl_error_string()) != false) {
        print $errstr . "\n";
    }
} else {
    readfile($decryptfile);
}

if (file_exists($cryptfile)) {
    echo "true\n";
}
if (file_exists($decryptfile)) {
    echo "true\n";
}
?>
--EXPECT--
bool(true)
Now is the winter of our discontent.
true
true
