--TEST--
GH-7902 (mb_send_mail may delimit headers with LF only)
--EXTENSIONS--
mbstring
--SKIPIF--
<?php
if (str_contains(getcwd(), " ")) die("skip sendmail_path ini with spaces");
?>
--INI--
sendmail_path={MAIL:mail.eml}
internal_encoding=UTF-8
--FILE--
<?php
$mail = Workspace::file('mail.eml');
chdir($mail->directory);
mb_language("uni");
$to = "omittedvalidaddress@example.com";
$subject = "test mail";
$message = "body of testing php mail";
$header["Mime-Version"] = "1.0";
$header["Content-Type"] = "text/html; charset=UTF-8";
$header["From"] = "omittedvalidaddress2@example.com";
$header["X-Mailer"] = "PHP/" . phpversion();
mb_send_mail($to, $subject, $message, $header);

$stream = fopen($mail, "rb");
$eml = stream_get_contents($stream);
fclose($stream);
var_dump(preg_match_all('/(?<!\r)\n/', $eml));
?>
--EXPECT--
int(0)
