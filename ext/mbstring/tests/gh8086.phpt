--TEST--
GH-8086 (mb_send_mail() function not working correctly in PHP 8.x)
--EXTENSIONS--
mbstring
--INI--
internal_encoding=UTF-8
sendmail_path={MAIL:mail.eml}
mail.mixed_lf_and_crlf=on
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
int(6)
