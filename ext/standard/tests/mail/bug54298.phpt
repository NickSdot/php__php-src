--TEST--
Bug #54298 (Using empty additional_headers adding extraneous CRLF)
--INI--
sendmail_path=tee bug54298.eml >/dev/null
mail.add_x_header = Off
--SKIPIF--
<?php
if (PHP_OS_FAMILY === 'Windows') die("skip Won't run on Windows");
?>
--FILE--
<?php
$outFile = TestPath::file('bug54298.eml');
chdir($outFile->directory);
var_dump(mail('someuser@example.com', 'testsubj', 'Body part', ''));
echo file_get_contents($outFile);
?>
--EXPECT--
bool(true)
To: someuser@example.com
Subject: testsubj

Body part
