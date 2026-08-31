--TEST--
GH-8086 (Mail() function not working correctly in PHP 8.x)
--INI--
sendmail_path={MAIL:gh8086.out}
mail.mixed_lf_and_crlf=on
--FILE--
<?php
$outFile = Workspace::file('gh8086.out');
chdir($outFile->directory);
var_dump(mail('user@example.com', 'Test Subject', 'A Message', 'KHeaders'));
$mail = file_get_contents($outFile);
var_dump(preg_match_all('/(?<!\r)\n/', $mail));
?>
--EXPECT--
bool(true)
int(5)
