--TEST--
Test trampoline for curl option CURLOPT_WRITEFUNCTION
--EXTENSIONS--
curl
--FILE--
<?php

class TrampolineTest {
    public function __call(string $name, array $arguments) {
        echo 'Trampoline for ', $name, PHP_EOL;
	    return 0;
    }
}
$o = new TrampolineTest();
$callback = [$o, 'trampoline'];

$log_file = TestPath::file('php-curl-CURLOPT_WRITEFUNCTION-trampoline')->path;

$fp = fopen($log_file, 'w+');
fwrite($fp, "test");
fclose($fp);

$ch = curl_init();
curl_setopt($ch, CURLOPT_WRITEFUNCTION, $callback);
curl_setopt($ch, CURLOPT_URL, 'file://' . $log_file);
curl_exec($ch);

?>
--EXPECT--
Trampoline for trampoline
