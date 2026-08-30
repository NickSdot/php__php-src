--TEST--
GH-19188: new INI mail.cr_lf_mode
--INI--
sendmail_path={MAIL:gh19188_cr_lf_mode.out}
mail.cr_lf_mode=crlf
--FILE--
<?php
$outFile = TestPath::file('gh19188_cr_lf_mode.out');
chdir($outFile->directory);

var_dump(mail('user@example.com', 'Test Subject', 'A Message', 'X-Test: crlf'));
$mail = file_get_contents($outFile);
echo "CRLF mode:\n";
var_dump(preg_match_all('/\r\n/', $mail));
var_dump(preg_match_all('/(?<!\r)\n/', $mail));
?>
--EXPECT--
bool(true)
CRLF mode:
int(5)
int(0)
