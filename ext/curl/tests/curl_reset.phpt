--TEST--
Test curl_reset
--EXTENSIONS--
curl
--FILE--
<?php

$test_file = TestPath::file('php-curl-test-output')->path;
$log_file = TestPath::file('php-curl-test-log')->path;

$fp = fopen($log_file, 'w+');
fwrite($fp, "test");
fclose($fp);

$testfile_fp = fopen($test_file, 'w+');

$ch = curl_init();
curl_setopt($ch, CURLOPT_FILE, $testfile_fp);
curl_setopt($ch, CURLOPT_URL, 'file://' . $log_file);
curl_exec($ch);

curl_reset($ch);
curl_setopt($ch, CURLOPT_URL, 'file://' . $log_file);
curl_exec($ch);

fclose($testfile_fp);

echo file_get_contents($test_file);

?>
--EXPECT--
testtest
