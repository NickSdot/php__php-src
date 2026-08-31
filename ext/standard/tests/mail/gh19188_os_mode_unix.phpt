--TEST--
GH-19188: mail.cr_lf_mode=os (Unix)
--SKIPIF--
<?php
if (PHP_OS_FAMILY === 'Windows') die("skip Non-Windows only");
?>
--INI--
sendmail_path={MAIL:gh19188_os_mode.out}
mail.cr_lf_mode=os
--FILE--
<?php
$outFile = Workspace::file('gh19188_os_mode.out');
chdir($outFile->directory);
var_dump(mail('user@example.com', 'Test Subject', 'A Message', 'X-Test: os'));
$mail = file_get_contents($outFile);
echo "OS mode:\n";
$crlf_count = preg_match_all('/\r\n/', $mail);
$lf_only_count = preg_match_all('/(?<!\r)\n/', $mail);
echo "CRLF count: ";
var_dump($crlf_count);
echo "LF-only count: ";
var_dump($lf_only_count);
?>
--EXPECT--
bool(true)
OS mode:
CRLF count: int(0)
LF-only count: int(5)
