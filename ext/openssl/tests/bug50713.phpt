--TEST--
Bug #50713 (openssl_pkcs7_verify() may ignore untrusted CAs)
--EXTENSIONS--
openssl
--FILE--
<?php
$inFile =  __DIR__ . "/plain.txt";
$outFile = Workspace::file();
$signersFile = Workspace::file();
$keyFile = Workspace::file();
$certFile = Workspace::file();
$cacertFile = Workspace::file();

include 'CertificateGenerator.inc';
$certificateGenerator = new CertificateGenerator();
$certificateGenerator->saveCaCert($cacertFile);
$certificateGenerator->saveNewCertAndKey('bug50713', $certFile, $keyFile, 1024);

var_dump(openssl_pkcs7_sign($inFile, $outFile, 'file://' . $certFile, 'file://' . $keyFile, [], PKCS7_NOCERTS));
var_dump(openssl_pkcs7_verify($outFile, 0, $signersFile, [$cacertFile], $certFile));
var_dump(strlen(file_get_contents($signersFile)) > 0);
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
