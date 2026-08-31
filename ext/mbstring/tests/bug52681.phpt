--TEST--
Bug #52681 (mb_send_mail() appends an extra MIME-Version header)
--EXTENSIONS--
mbstring
--SKIPIF--
<?php
if (!function_exists("mb_send_mail") || !mb_language("neutral")) {
    die("skip mb_send_mail() not available");
}
?>
--INI--
sendmail_path={MAIL:mail.eml}
mail.add_x_header=off
--FILE--
<?php
$mail = Workspace::file('mail.eml');
chdir($mail->directory);
$to = 'example@example.com';
$headers = 'MIME-Version: 2.0';

mb_send_mail($to, mb_language(), "test", $headers);

readfile($mail);
?>
--EXPECTF--
To: example@example.com
Subject: %s
MIME-Version: 2.0
Content-Type: text/plain; charset=%s
Content-Transfer-Encoding: %s

%s
