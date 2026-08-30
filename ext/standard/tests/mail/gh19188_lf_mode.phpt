--TEST--
GH-19188: mail.cr_lf_mode=lf
--INI--
sendmail_path={MAIL:gh19188_lf_mode.out}
mail.cr_lf_mode=lf
--FILE--
<?php
$outFile = TestPath::file('gh19188_lf_mode.out');
chdir($outFile->directory);

var_dump(mail('user@example.com', 'Test Subject', "A Message\r\nWith CRLF", 'X-Test: lf'));
$mail = file_get_contents($outFile);
echo "LF mode:\n";
var_dump(preg_match_all('/\r\n/', $mail));
var_dump(preg_match_all('/(?<!\r)\n/', $mail));
?>
--EXPECT--
bool(true)
LF mode:
int(0)
int(6)
