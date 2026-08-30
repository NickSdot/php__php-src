--TEST--
Bug #47983 (mixed LF and CRLF line endings in mail())
--INI--
sendmail_path={MAIL:bug47983.out}
--FILE--
<?php
$outFile = TestPath::file('bug47983.out');
chdir($outFile->directory);
var_dump(mail('user@example.com', 'Test Subject', 'A Message', 'KHeaders'));
$mail = file_get_contents($outFile);
var_dump(preg_match_all('/(?<!\r)\n/', $mail));
?>
--EXPECT--
bool(true)
int(0)
