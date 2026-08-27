--TEST--
Test curl option CURLOPT_FILE
--CREDITS--
Mathieu Kooiman <mathieuk@gmail.com>
Dutch UG, TestFest 2009, Utrecht
--DESCRIPTION--
Writes the value 'test' to a temporary file. Use curl to access this file and store the output in another temporary file. Tests the PHP_CURL_FILE case in curl_write().
--EXTENSIONS--
curl
--FILE--
<?php

$test_file = TestPath::file();
$log_file = TestPath::file();

$fp = fopen($log_file, 'w+');
fwrite($fp, "test");
fclose($fp);

$testfile_fp = fopen($test_file, 'w+');

$ch = curl_init();
curl_setopt($ch, CURLOPT_FILE, $testfile_fp);
curl_setopt($ch, CURLOPT_URL, 'file://' . $log_file);
curl_exec($ch);

fclose($testfile_fp);

echo file_get_contents($test_file);

?>
--EXPECT--
test
